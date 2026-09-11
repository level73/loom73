import { cp, mkdir, readdir, readFile, writeFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { optimize } from 'svgo';
import sharp from 'sharp';

/** Define SRC Paths **/
const frontendRoot = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(frontendRoot, '..');
const sourceAssetsRoot = path.join(frontendRoot, 'assets');
const publicRoot = path.join(projectRoot, 'public_html', 'public');

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
        `[build] ${copiedAssets} static ${label} copied.`
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
        `[build] ${svgAssets.length} SVG ${label} optimized. ` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%).`
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

    if (!Number.isInteger(quality) || quality < 1 ||quality > 100) {
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
        `at WebP quality ${quality}. ` +
        `${savedBytes.toLocaleString('en-US')} bytes saved ` +
        `(${savedPercentage.toFixed(1)}%). ` +
        `${copiedAssets} existing WebP copied.`
    );
}

/** Register Tasks **/
const tasks = new Map([
    [ 'assets:static', copyStaticAssets,],
    ['assets:svg', optimizeSvgAssets],
    ['assets:raster', processRasterAssets],
]);

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
        const message = error instanceof Error
            ? error.message
            : String(error);

        console.error(`[build] ${message}`);
        process.exitCode = 1;
    }
}


await main();
