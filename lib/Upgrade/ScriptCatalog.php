<?php

namespace Loom73\Upgrade;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;

final class ScriptCatalog
{
    public static function discover(): array
    {
        $root = ROOT_DIR . '/updates/core';

        if (!is_dir($root)):
            return [];
        endif;

        $resolvedRoot = realpath($root);

        if ($resolvedRoot === false):
            throw new RuntimeException('Cannot read the core update directory.');
        endif;

        $prefix = rtrim(str_replace('\\', '/', $resolvedRoot), '/') . '/';
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
        );

        $scripts = [];
        $orders = [];

        foreach ($iterator as $file):
            if ($file->isLink()):
                throw new RuntimeException('Update scripts cannot be symlinks.');
            endif;

            if (!$file->isFile() || strtolower($file->getExtension()) !== 'php'):
                continue;
            endif;

            $path = str_replace('\\', '/', $file->getPathname());
            $relative = substr($path, strlen($prefix));

            if (!preg_match(
                '~^([0-9]+\.[0-9]+\.[0-9]+)/([0-9]{3})_[a-z0-9_]+\.php$~',
                $relative,
                $matches
            )):
                throw new RuntimeException("Invalid core update path: {$relative}");
            endif;

            $version = $matches[1];
            $order = (int) $matches[2];

            if ($order === 0 || isset($orders[$version][$order])):
                throw new RuntimeException("Duplicate or invalid update order: {$relative}");
            endif;

            $orders[$version][$order] = true;

            $id = 'core/' . $version . '/' . substr(basename($relative), 0, -4);

            if (strlen($id) > 160):
                throw new RuntimeException("Update ID exceeds 160 characters: {$id}");
            endif;

            $contents = file_get_contents($file->getPathname());

            if ($contents === false):
                throw new RuntimeException("Cannot read core update: {$relative}");
            endif;

            $checksum = hash('sha256', str_replace("\r\n", "\n", $contents));

            if ($checksum === false):
                throw new RuntimeException("Cannot hash core update: {$relative}");
            endif;

            $scripts[] = [
                'update_id' => $id,
                'kind' => 'schema',
                'loom73_version' => $version,
                'checksum_sha256' => $checksum,
                'path' => $file->getPathname(),
                'order' => $order,
            ];
        endforeach;

        usort(
            $scripts,
            static function (array $left, array $right): int {
                $byVersion = version_compare(
                    $left['loom73_version'],
                    $right['loom73_version']
                );

                return $byVersion !== 0
                    ? $byVersion
                    : $left['order'] <=> $right['order'];
            }
        );

        return $scripts;
    }
}