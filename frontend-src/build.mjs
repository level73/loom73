import { cp, mkdir, readdir, readFile, writeFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { optimize } from 'svgo';
import sharp from 'sharp';
import { transform as transformCss} from 'lightningcss';
import { transform as transformJavaScript } from 'esbuild';
import { watch as watchFileSystem } from 'node:fs';

/** Define SRC Paths **/
const frontendRoot = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(frontendRoot, '..');
const sourceAssetsRoot = path.join(frontendRoot, 'assets');
const publicRoot = path.join(projectRoot, 'public_html', 'public');
const sourceCssRoot = path.join(frontendRoot, 'css');
const sourceJavaScriptRoot = path.join(frontendRoot, 'js');

/** Manifest path **/
const projectManifestPath = path.join(projectRoot, 'package.json');

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

    console.log(
        `[build] ${copiedAssets} static ${label} copied.\n` +
        `-------------------------------------------------`
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

    console.log(
        `[build] ${svgAssets.length} SVG ${label} optimized. \n` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).\n` +
        `-------------------------------------------------`
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
    console.log(
        `[build] ${optimizedAssets} raster assets optimized ` +
        `at WebP quality ${quality}. \n` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%). \n` +
        `${copiedAssets} existing WebP copied.\n` +
        `-------------------------------------------------`
    );
}

/** CSS Concat and Minify **/
/** CSS Build Options **/
const cssSourceFiles = [
    '001-layers.css',
    '002-reset.css',
    '003-layout.css',
    '004-components.css',
    '005-ui.css',
    '006-utilities.css',
    '007-specific.css',
    '008-stitch.css',
];

const cssBuildOptions = {
    minify: true,
};
function annotateCssError(error, sources) {
    if (
        typeof error !== 'object' ||
        error === null ||
        !Number.isInteger(error.loc?.line)
    ) {
        return;
    }

    let startLine = 1;

    for (const source of sources) {
        const lineBreaks = (
            source.content.match(/\r\n|\r|\n/g) ?? []
        ).length;

        const endLine = startLine + lineBreaks;

        if (
            error.loc.line >= startLine &&
            error.loc.line <= endLine
        ) {
            error.fileName = source.filePath;
            error.loc = {
                ...error.loc,
                line: error.loc.line - startLine + 1,
            };
            error.source = source.content;

            return;
        }

        /*
         * Account for the newline inserted by join('\n').
         */
        startLine = endLine + 1;
    }
}
async function buildCss(outputRoot = publicRoot) {
    const sources = await Promise.all(
        cssSourceFiles.map(async (file) => {
            const filePath = path.join(sourceCssRoot, file);

            return {
                filePath,
                content: await readFile(filePath, 'utf8'),
            };
        })
    );

    /*
     * The separator prevents the end of one source file from touching
     * the beginning of the next one.
     */
    const sourceCss = sources.map((source) => source.content).join('\n');
    const destination = path.join(
        outputRoot,
        'css',
        'main.min.css'
    );

    let result;

    try {
        result = transformCss({
            filename: destination,
            code: Buffer.from(sourceCss, 'utf8'),
            ...cssBuildOptions,
        });
    } catch (error) {
        annotateCssError(error, sources);
        throw error;
    }

    await mkdir(path.dirname(destination), {
        recursive: true,
    });

    await writeFile(destination, result.code);

    const sourceBytes = Buffer.byteLength(sourceCss, 'utf8');
    const outputBytes = result.code.length;
    const savedBytes = sourceBytes - outputBytes;
    const savedPercentage = sourceBytes === 0 ? 0 : (savedBytes / sourceBytes) * 100;

    const label = cssSourceFiles.length === 1
        ? 'file'
        : 'files';

    console.log(
        `[build] ${cssSourceFiles.length} CSS ${label} built. \n` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).\n` +
        `-------------------------------------------------`
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
    /** Get Project Version **/
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

    console.log(
        `[build] Loom73 ${projectVersion}: ${javascriptEntries.length} JavaScript ${label} built. \n` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).\n` +
        `-------------------------------------------------`
    );
}
/** Aggregated Tasks **/
async function optimizeAssets(outputRoot = publicRoot, showVersion = true ) {
    if (showVersion) {
        const projectVersion = await getProjectVersion();
        console.log(`Loom73 v. ${projectVersion}`);
    }

    console.log('[optimize] Asset optimization started.');

    await copyStaticAssets(outputRoot);
    await optimizeSvgAssets(outputRoot);
    await processRasterAssets(outputRoot);

    console.log('[optimize] Optimization completed.');
}

async function buildProject(outputRoot = publicRoot) {
    const projectVersion = await getProjectVersion();

    console.log(`Loom73 v. ${projectVersion}`);
    console.log('[build] Build started.');

    await buildCss(outputRoot);
    await buildJavaScript(outputRoot);
    await optimizeAssets(outputRoot, false);

    console.log('[build] Build completed.');
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

        watchTaskQueue = watchTaskQueue
            .then(async () => {
                console.log(`[watch] Running ${taskName}.`);
                await task();
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
        console.error(
            `[build] Available tasks: ${[...tasks.keys()].join(', ')}`
        );
        process.exitCode = 1;
        return;
    }

    try {
        await task();
    } catch (error) {
        console.error(formatBuildError(error));
        process.exitCode = 1;
    }
}


await main();
