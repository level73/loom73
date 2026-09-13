import { cp, mkdir, readdir, readFile, writeFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { styleText } from 'node:util';
import { watch as watchFileSystem } from 'node:fs';
import { performance } from 'node:perf_hooks';
import { optimize } from 'svgo';
import sharp from 'sharp';
import { bundleAsync } from 'lightningcss';
import { transform as transformJavaScript } from 'esbuild';



/** Define SRC Paths **/
const frontendRoot = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(frontendRoot, '..');
const sourceAssetsRoot = path.join(frontendRoot, 'assets');
const publicRoot = path.join(projectRoot, 'public_html', 'public');
const sourceCssRoot = path.join(frontendRoot, 'css');
const sourceJavaScriptRoot = path.join(frontendRoot, 'js');

/** Manifest path **/
const projectManifestPath = path.join(projectRoot, 'package.json');

/** Console Output **/
function formatDuration(startedAt) {
    return (
        (performance.now() - startedAt) / 1000
    ).toFixed(2);
}
function logSuccess(message, startedAt) {
    const output =
        `✓ ${message} ` +
        `Task took ${formatDuration(startedAt)} seconds.`;

    console.log(
        styleText(
            ['bold', 'green'],
            output,
            {
                stream: process.stdout,
            }
        )
    );
}
function logFailure(message, startedAt = null) {
    const duration = Number.isFinite(startedAt)
        ? `\nTask failed after ${formatDuration(startedAt)} seconds.`
        : '';

    console.error(
        styleText(
            ['bold', 'red'],
            `✗ ${message}${duration}`,
            {
                stream: process.stderr,
            }
        )
    );
}

/** Project version **/
async function getProjectVersion() {
    const manifestSource = await readFile(
        projectManifestPath,
        'utf8'
    );

    const manifest = JSON.parse(manifestSource);

    if (
        typeof manifest.version !== 'string' ||
        manifest.version.trim() === ''
    ) {
        throw new Error(
            `Missing or invalid version in ${projectManifestPath}.`
        );
    }

    return manifest.version;
}

/** Static asset dirs: add here in you want to copy other assets to public dir **/
const staticAssetDirectories = [
    'favicon',
    'fonts',
];
async function copyStaticAssets(outputRoot = publicRoot) {
    const startedAt = performance.now();
    const outputAssetsRoot = path.join(outputRoot, 'assets');
    let copiedAssets = 0;
    await mkdir(outputAssetsRoot, { recursive: true });

    for (const directory of staticAssetDirectories) {
        const source = path.join(sourceAssetsRoot, directory);
        const destination = path.join(outputAssetsRoot, directory);
        const entries = await readdir(source, {
                recursive: true,
                withFileTypes: true,
            }
        );
        copiedAssets += entries.filter(entry => entry.isFile()).length;
        await cp(source, destination, {
                    recursive: true,
                    force: true,
                }
        );
    }

    const label = copiedAssets === 1 ? 'asset' : 'assets';

    logSuccess(
        `[assets:static] ${copiedAssets} static ${label} copied.`,
        startedAt
    );
}

/** SVG Optimization Options **/
const svgOptimizationOptions = {
    plugins: [
        {
            name: 'preset-default',
            params: {
                overrides: {removeViewBox: false,},
            },
        },
    ],
};
async function optimizeSvgAssets(outputRoot = publicRoot) {
    const startedAt = performance.now();
    const entries = await readdir(sourceAssetsRoot, {
        recursive: true,
        withFileTypes: true,
    });

    const svgAssets = entries.filter((entry) => {
        if (!entry.isFile() || path.extname(entry.name).toLowerCase() !== '.svg') {
            return false;
        }

        const source = path.join(entry.parentPath, entry.name);
        const relativePath = path.relative(sourceAssetsRoot, source);
        const [topLevelDirectory] = relativePath.split(path.sep);

        return !staticAssetDirectories.includes(topLevelDirectory);
    });

    let sourceBytes = 0;
    let optimizedBytes = 0;

    for (const entry of svgAssets) {
        const source = path.join(entry.parentPath, entry.name);
        const relativePath = path.relative(sourceAssetsRoot, source);
        const destination = path.join(
            outputRoot,
            'assets',
            relativePath
        );

        const sourceSvg = await readFile(source, 'utf8');
        const result = optimize(sourceSvg, {
            ...svgOptimizationOptions,
            path: source,
        });

        sourceBytes += Buffer.byteLength(sourceSvg, 'utf8');
        optimizedBytes += Buffer.byteLength(result.data, 'utf8');

        await mkdir(path.dirname(destination), {
            recursive: true,
        });

        await writeFile(destination, result.data, 'utf8');
    }

    const savedBytes = sourceBytes - optimizedBytes;
    const savedPercentage = sourceBytes === 0 ? 0 : (savedBytes / sourceBytes) * 100;

    const label = svgAssets.length === 1 ? 'asset' : 'assets';

    logSuccess(
        `[assets:svg] ${svgAssets.length} SVG ${label} optimized.  ` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).`,
        startedAt
    );
}

/** Raster Optimization Options **/
const rasterOptimizationOptions = {
    webp: {
        quality: 75,
    },
};
const convertibleRasterExtensions = new Set([
    '.gif',
    '.jpeg',
    '.jpg',
    '.png',
]);
const readyRasterExtensions = new Set([
    '.webp',
]);
async function processRasterAssets(outputRoot = publicRoot) {
    const startedAt = performance.now();

    const quality = rasterOptimizationOptions.webp.quality;

    if (!Number.isInteger(quality) || quality < 1 || quality > 100) {
        throw new Error(
            'WebP quality must be an integer between 1 and 100.'
        );
    }

    const entries = await readdir(sourceAssetsRoot, {
        recursive: true,
        withFileTypes: true,
    });

    const rasterAssets = entries.filter((entry) => {
        if (!entry.isFile()) {
            return false;
        }

        const source = path.join(entry.parentPath, entry.name);
        const relativePath = path.relative(sourceAssetsRoot, source);
        const [topLevelDirectory] = relativePath.split(path.sep);
        const extension = path.extname(entry.name).toLowerCase();

        if (staticAssetDirectories.includes(topLevelDirectory)) {
            return false;
        }
        return (
            convertibleRasterExtensions.has(extension) ||
            readyRasterExtensions.has(extension)
        );
    });

    let optimizedAssets = 0;
    let copiedAssets = 0;
    let sourceBytes = 0;
    let optimizedBytes = 0;

    for (const entry of rasterAssets) {
        const source = path.join(entry.parentPath, entry.name);
        const relativePath = path.relative(sourceAssetsRoot, source);
        const extension = path.extname(entry.name).toLowerCase();

        if (readyRasterExtensions.has(extension)) {
            const destination = path.join(outputRoot,'assets',relativePath);

            await mkdir(path.dirname(destination), {
                recursive: true,
            });

            await cp(source, destination, {
                force: true,
            });

            copiedAssets++;
            continue;
        }

        const parsedPath = path.parse(relativePath);
        const destination = path.join(outputRoot,'assets',parsedPath.dir,`${parsedPath.name}.webp`);

        await mkdir(path.dirname(destination), {
            recursive: true,
        });

        const sourceSize = (await stat(source)).size;

        const result = await sharp(
                source,
                { animated: true,}
            )
            .webp(rasterOptimizationOptions.webp)
            .toFile(destination);

        sourceBytes += sourceSize;
        optimizedBytes += result.size;
        optimizedAssets++;
    }

    const savedBytes = sourceBytes - optimizedBytes;
    const savedPercentage = sourceBytes === 0 ? 0 : (savedBytes / sourceBytes) * 100;
    logSuccess(
        `[assets:raster] ${optimizedAssets} raster assets optimized ` +
        `at WebP quality ${quality}. ` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%). ` +
        `${copiedAssets} existing WebP copied.`,
        startedAt
    );
}


/** CSS Bundle and Minify **/
const cssEntryPath = path.join(
    sourceCssRoot,
    'index.css'
);

const cssBuildOptions = {
    minify: true,
};

async function buildCss(outputRoot = publicRoot) {
    const startedAt = performance.now();

    /*
     * Store every source read by Lightning CSS. This provides
     * automatic file counts, byte totals and source-aware errors
     * without duplicating the import graph inside build.mjs.
     */
    const sources = new Map();

    const destination = path.join(
        outputRoot,
        'css',
        'main.min.css'
    );

    let result;

    try {
        result = await bundleAsync({
            filename: cssEntryPath,
            ...cssBuildOptions,

            resolver: {
                async read(filePath) {
                    const source = await readFile(
                        filePath,
                        'utf8'
                    );

                    sources.set(
                        path.resolve(filePath),
                        source
                    );

                    return source;
                },
            },
        });
    } catch (error) {
        /*
         * bundleAsync already reports the real imported filename
         * and line. Restore its source text so formatBuildError()
         * can also print the offending line and caret.
         */
        if (
            typeof error === 'object' &&
            error !== null &&
            typeof error.fileName === 'string'
        ) {
            const source = sources.get(
                path.resolve(error.fileName)
            );

            if (source !== undefined) {
                error.source = source;
            }
        }

        throw error;
    }

    await mkdir(path.dirname(destination), {
        recursive: true,
    });

    await writeFile(destination, result.code);

    const sourceBytes = [...sources.values()]
        .reduce(
            (total, source) => {
                return total + Buffer.byteLength(
                    source,
                    'utf8'
                );
            },
            0
        );

    const outputBytes = result.code.length;
    const savedBytes = sourceBytes - outputBytes;
    const savedPercentage = sourceBytes === 0
        ? 0
        : (savedBytes / sourceBytes) * 100;

    const cssFileCount = sources.size;
    const label = cssFileCount === 1
        ? 'file'
        : 'files';

    logSuccess(
        `[css] ${cssFileCount} CSS ${label} bundled and minified. ` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).`,
        startedAt
    );
}

/** JavaScript Build Options **/
const javascriptEntries = [
    {
        source: 'index.js',
        destination: 'index.min.js',
    },
    {
        source: 'forms.js',
        destination: 'forms.min.js',
    },
    {
        source: 'table.js',
        destination: 'table.min.js',
    },
    {
        source: 'ui.js',
        destination: 'ui.min.js',
    },
];
const javascriptBuildOptions = {
    charset: 'utf8',
    format: 'esm',
    legalComments: 'none',
    minify: true,
    sourcemap: false,
    target: 'esnext',
};
async function buildJavaScript(outputRoot = publicRoot) {
    const startedAt = performance.now();
    const projectVersion = await getProjectVersion();
    const outputJavaScriptRoot = path.join(
        outputRoot,
        'js'
    );

    await mkdir(outputJavaScriptRoot, {
        recursive: true,
    });

    let sourceBytes = 0;
    let outputBytes = 0;

    for (const entry of javascriptEntries) {
        const source = path.join(
            sourceJavaScriptRoot,
            entry.source
        );

        const destination = path.join(
            outputJavaScriptRoot,
            entry.destination
        );

        const sourceJavaScript = await readFile(
            source,
            'utf8'
        );

        const result = await transformJavaScript(
            sourceJavaScript,
            {
                ...javascriptBuildOptions,
                define: {
                    __LOOM73_VERSION__: JSON.stringify(projectVersion),
                },
                sourcefile: source,
            }
        );

        await writeFile(destination, result.code, 'utf8');

        sourceBytes += Buffer.byteLength(
            sourceJavaScript,
            'utf8'
        );

        outputBytes += Buffer.byteLength(
            result.code,
            'utf8'
        );
    }

    const savedBytes = sourceBytes - outputBytes;
    const savedPercentage = sourceBytes === 0
        ? 0
        : (savedBytes / sourceBytes) * 100;

    const label = javascriptEntries.length === 1
        ? 'file'
        : 'files';

    logSuccess(
        `[js] ${javascriptEntries.length} JavaScript ${label} built. ` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).`,
        startedAt
    );
}
/** Aggregated Tasks **/
async function optimizeAssets(outputRoot = publicRoot, showVersion = true ) {
    const startedAt = performance.now();
    if (showVersion) {
        const projectVersion = await getProjectVersion();
        console.log(`Loom73 v. ${projectVersion}`);
    }

    console.log('[optimize] Asset optimization started.');

    await copyStaticAssets(outputRoot);
    await optimizeSvgAssets(outputRoot);
    await processRasterAssets(outputRoot);

    logSuccess(
        '[optimize] Asset optimization completed.',
        startedAt
    );
}

async function buildProject(outputRoot = publicRoot) {
    const startedAt = performance.now();
    const projectVersion = await getProjectVersion();

    console.log(`Loom73 v. ${projectVersion}`);
    console.log('[build] Build started.');

    await buildCss(outputRoot);
    await buildJavaScript(outputRoot);
    await optimizeAssets(outputRoot, false);

    logSuccess(
        '[build] Build completed.',
        startedAt
    );
}

/** Watcher **/
/** Watch Options **/
const watchDebounceMilliseconds = 150;
const scheduledWatchTasks = new Map();

let watchTaskQueue = Promise.resolve();

function scheduleWatchTask(taskName, task) {
    const scheduledTask = scheduledWatchTasks.get(taskName);

    if (scheduledTask) {
        clearTimeout(scheduledTask);
    }

    const timeout = setTimeout(() => {
        scheduledWatchTasks.delete(taskName);

        watchTaskQueue = watchTaskQueue.then(async () => {
                const startedAt = performance.now();
                console.log(`[watch] Running ${taskName}.`);
                try {
                    await task();
                } catch (error) {
                    logFailure(
                        formatBuildError(error),
                        startedAt
                    );
                }
            })
            .catch((error) => {
                console.error(formatBuildError(error));
            });
    }, watchDebounceMilliseconds);

    scheduledWatchTasks.set(taskName, timeout);
}
function scheduleAssetTask(fileName) {
    if (!fileName) {
        scheduleWatchTask('optimize', optimizeAssets);
        return;
    }

    const relativePath = String(fileName);
    const [topLevelDirectory] = relativePath.split(/[\\/]/);
    const extension = path.extname(relativePath).toLowerCase();

    if (staticAssetDirectories.includes(topLevelDirectory)) {
        scheduleWatchTask(
            'assets:static',
            copyStaticAssets
        );
        return;
    }

    if (extension === '.svg') {
        scheduleWatchTask(
            'assets:svg',
            optimizeSvgAssets
        );
        return;
    }

    if (
        convertibleRasterExtensions.has(extension) ||
        readyRasterExtensions.has(extension)
    ) {
        scheduleWatchTask(
            'assets:raster',
            processRasterAssets
        );
    }
}
async function watchSources() {
    await buildProject();

    const watchers = [
        watchFileSystem(
            sourceCssRoot,
            {
                recursive: true,
            },
            () => {
                scheduleWatchTask('css', buildCss);
            }
        ),

        watchFileSystem(
            sourceJavaScriptRoot,
            {
                recursive: true,
            },
            () => {
                scheduleWatchTask(
                    'js',
                    buildJavaScript
                );
            }
        ),

        watchFileSystem(
            sourceAssetsRoot,
            {
                recursive: true,
            },
            (eventType, fileName) => {
                scheduleAssetTask(fileName);
            }
        ),
    ];

    console.log(
        '[watch] Watching CSS, JavaScript and assets. ' +
        'Press Ctrl+C to stop.'
    );

    await new Promise((resolve, reject) => {
        const closeWatchers = () => {
            for (const watcher of watchers) {
                watcher.close();
            }

            for (const timeout of scheduledWatchTasks.values()) {
                clearTimeout(timeout);
            }

            scheduledWatchTasks.clear();
        };

        const stopWatching = () => {
            closeWatchers();
            console.log('\n[watch] Stopped.');
            resolve();
        };

        for (const watcher of watchers) {
            watcher.once('error', (error) => {
                closeWatchers();
                reject(error);
            });
        }

        process.once('SIGINT', stopWatching);
        process.once('SIGTERM', stopWatching);
    });
}

/** Register Tasks **/
const tasks = new Map([
    [ 'build', buildProject ],
    [ 'optimize', optimizeAssets ],
    [ 'watch', watchSources ],

    [ 'assets:static', copyStaticAssets ],
    [ 'assets:svg', optimizeSvgAssets ],
    [ 'assets:raster', processRasterAssets ],
    [ 'css', buildCss ],
    [ 'js', buildJavaScript ],
]);

/** Build error message formatter **/
function formatBuildError(error) {
    if (!(error instanceof Error)) {
        return `[build] ${String(error)}`;
    }

    const diagnostic = Array.isArray(error.errors)
        ? error.errors[0]
        : null;

    if (diagnostic?.location) {
        const location = diagnostic.location;
        const lineLabel = String(location.line);

        const output = [
            `[build] ${diagnostic.text}`,
            `[build] ${location.file}:${location.line}:${location.column + 1}`,
            `${lineLabel} | ${location.lineText}`,
            `${' '.repeat(lineLabel.length)} | ` +
            `${' '.repeat(location.column)}^`,
        ];

        return output.join('\n');
    }

    const output = [
        `[build] ${error.message}`,
    ];

    const fileName = error.fileName;
    const line = error.loc?.line;
    const column = error.loc?.column;

    if (fileName && Number.isInteger(line)) {
        const location = Number.isInteger(column)
            ? `${fileName}:${line}:${column + 1}`
            : `${fileName}:${line}`;

        output.push(`[build] ${location}`);
    }

    if (
        typeof error.source === 'string' &&
        Number.isInteger(line)
    ) {
        const sourceLine = error.source
            .split(/\r\n|\r|\n/)[line - 1];

        if (sourceLine !== undefined) {
            const lineLabel = String(line);

            output.push(`${lineLabel} | ${sourceLine}`);

            if (Number.isInteger(column)) {
                output.push(
                    `${' '.repeat(lineLabel.length)} | ` +
                    `${' '.repeat(column)}^`
                );
            }
        }
    }

    return output.join('\n');
}

async function main() {
    const taskName = process.argv[2];
    const task = tasks.get(taskName);

    if (!task) {
        logFailure(
            `[build] Available tasks: ${[...tasks.keys()].join(', ')}`
        );
        process.exitCode = 1;
        return;
    }

    const startedAt = performance.now();

    try {
        await task();
    } catch (error) {
        logFailure(
            formatBuildError(error),
            startedAt
        );

        process.exitCode = 1;
    }
}
await main();
