<?php

namespace Loom73\Upgrade;

final class ScriptPlanner
{
    public static function build(
        array $scripts,
        array $records,
        string $baseline,
        string $target
    ): array {
        if (version_compare($baseline, $target, '>')):
            throw new \RuntimeException(
                "Schema baseline {$baseline} is newer than code {$target}."
            );
        endif;

        $registered = [];
        $errors = [];

        foreach ($records as $record):
            if ($record['kind'] === 'baseline' || $record['kind'] === 'registry'):
                continue;
            endif;

            if ($record['kind'] !== 'schema'):
                $errors[] = "Unknown update kind: {$record['kind']}";
                continue;
            endif;

            $registered[$record['update_id']] = $record;
        endforeach;

        $items = [];
        $pendingSeen = false;

        foreach ($scripts as $script):
            $id = $script['update_id'];
            $version = $script['loom73_version'];
            $record = $registered[$id] ?? null;

            if ($record !== null):
                unset($registered[$id]);

                $valid =
                    $record['loom73_version'] === $version
                    && $record['checksum_sha256'] === $script['checksum_sha256'];

                if (!$valid):
                    $errors[] = "Recorded update differs from file: {$id}";
                endif;

                if (version_compare($version, $baseline, '<=')):
                    $errors[] = "Update recorded within baseline: {$id}";
                    $valid = false;
                endif;

                if (version_compare($version, $target, '>')):
                    $errors[] = "Recorded update is newer than code: {$id}";
                    $valid = false;
                endif;

                if ($pendingSeen):
                    $errors[] = "Update applied after a missing earlier update: {$id}";
                    $valid = false;
                endif;

                $status = $valid ? 'applied' : 'invalid';
            elseif (version_compare($version, $baseline, '<=')):
                $status = 'included';
            elseif (version_compare($version, $target, '>')):
                $status = 'future';
            else:
                $status = 'pending';
                $pendingSeen = true;
            endif;

            $items[] = ['status' => $status, 'script' => $script];
        endforeach;

        foreach ($registered as $id => $record):
            $errors[] = "Recorded update file is missing: {$id}";
        endforeach;

        return ['items' => $items, 'errors' => $errors];
    }
}