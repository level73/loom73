import { cp, mkdir, readdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { optimize } from 'svgo';

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



const tasks = new Map([
    [ 'assets:static', copyStaticAssets,],
    ['assets:svg', optimizeSvgAssets],
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
