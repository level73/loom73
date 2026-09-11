import { cp, mkdir, readdir } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

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


const tasks = new Map([
    [ 'assets:static', copyStaticAssets,],
]);


async function main() {
    const taskName = process.argv[2];
    const task = tasks.get(taskName);

    if (!task) {
        console.error(
            '[build] Usage: node frontend-src/build.mjs assets:static'
        );
        process.exitCode = 1;
        return;
    }

    try {
        await task();
    } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        console.error(
            `[build] Usage: node frontend-src/build.mjs assets:static'`
        );
        process.exitCode = 1;
    }
}


await main();
