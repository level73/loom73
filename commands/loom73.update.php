<?php

namespace Loom73\Shuttle;

use Loom73\Beam\Connection;
use Loom73\Upgrade\SchemaBaseline62;
use Loom73\Upgrade\SchemaInspector;
use Loom73\Upgrade\UpdateRegistry;
use Loom73\Upgrade\ScriptCatalog;
use Loom73\Upgrade\ScriptPlanner;
use PDO;

final class Loom73Update
{
    public function __construct(array $args = [])
    {
        $isPlan = $args === ['--plan'];

        if ($args !== [] && !$isPlan):
            fwrite(STDERR, "Usage: php shuttle loom73.update [--plan]" . PHP_EOL);
            exit(2);
        endif;

        $CLI = new CLI();

        try {
            $version = \getLoom73Version();

            if (!is_string($version) || $version === ''):
                throw new \RuntimeException('Loom73 version missing from package.json.');
            endif;

            echo "Loom73 code version: {$version}" . PHP_EOL;

            $this->run(Connection::pdo(), $CLI, $isPlan, $version);

        } catch (\Throwable $e) {
            fwrite(
                STDERR,
                $CLI->cout_color($e->getMessage(), 'red') . PHP_EOL
            );
            exit(1);
        }
    }

    private function run(PDO $pdo, CLI $CLI, bool $isPlan, string $target): void
    {
        $registryType = $pdo->query(
            "SELECT TABLE_TYPE
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'core_updates'"
        )->fetchColumn();

        if ($registryType === false):
            $report = SchemaInspector::inspectBaseline62($pdo);

            foreach ($report['errors'] as $error):
                fwrite(STDERR, $CLI->cout_color($error, 'red') . PHP_EOL);
            endforeach;

            foreach ($report['warnings'] as $warning):
                echo $CLI->cout_color($warning, 'yellow') . PHP_EOL;
            endforeach;

            if ($report['errors'] !== [] || $report['warnings'] !== []):
                throw new \RuntimeException(
                    'The 6.2.0 baseline requires manual review.'
                );
            endif;

            echo $CLI->cout_color(
                    'Registry absent; schema compatible with baseline '
                    . SchemaBaseline62::VERSION . '.',
                    'green'
                ) . PHP_EOL;

            $plan = $this->showScriptPlan($CLI, [], SchemaBaseline62::VERSION, $target);

            if ($isPlan):
                echo 'Bootstrap required; no changes made.' . PHP_EOL;
                return;
            endif;

            $pdo->exec(UpdateRegistry::CREATE_SQL);
            UpdateRegistry::recordBaseline($pdo, SchemaBaseline62::VERSION);

            echo $CLI->cout_color(
                    'Registry created; baseline '
                    . SchemaBaseline62::VERSION
                    . ' recorded.',
                    'green'
                ) . PHP_EOL;

            $this->applyPending($pdo, $CLI, $plan);

            return;
        endif;

        if ($registryType !== 'BASE TABLE'):
            throw new \RuntimeException(
                'core_updates exists but is not a table.'
            );
        endif;

        $actualColumns = $pdo->query(
            "SELECT COLUMN_NAME
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'core_updates'"
        )->fetchAll(PDO::FETCH_COLUMN);

        $expectedColumns = UpdateRegistry::COLUMNS;

        if (
            array_diff($expectedColumns, $actualColumns) !== []
            || array_diff($actualColumns, $expectedColumns) !== []
        ):
            throw new \RuntimeException(
                'Unknown core_updates structure; update stopped.'
            );
        endif;

        $baseline = $pdo->query(
            "SELECT `kind`, `loom73_version`, `checksum_sha256`
             FROM `core_updates`
             WHERE `update_id` = 'baseline'"
        )->fetch(PDO::FETCH_ASSOC);

        if (
            $baseline === false
            || $baseline['kind'] !== 'baseline'
            || !is_string($baseline['loom73_version'])
            || $baseline['loom73_version'] === ''
            || $baseline['checksum_sha256'] !== null
        ):
            throw new \RuntimeException(
                'core_updates has no valid baseline; update stopped.'
            );
        endif;

        echo $CLI->cout_color(
                'Registered schema baseline: ' . $baseline['loom73_version'],
                'green'
            ) . PHP_EOL;

        $records = $pdo->query(
            "SELECT `update_id`, `kind`, `loom73_version`, `checksum_sha256`
                     FROM `core_updates`
                     WHERE `update_id` <> 'baseline'"
        )->fetchAll(PDO::FETCH_ASSOC);

        $plan = $this->showScriptPlan(
            $CLI,
            $records,
            $baseline['loom73_version'],
            $target
        );

        if ($isPlan):
            echo "No changes made." . PHP_EOL;
            return;
        endif;

        $this->applyPending($pdo, $CLI, $plan);
    }


    private function showScriptPlan(
        CLI $CLI,
        array $records,
        string $baseline,
        string $target
    ): array {
        $plan = ScriptPlanner::build(
            ScriptCatalog::discover(),
            $records,
            $baseline,
            $target
        );

        $pending = 0;

        foreach ($plan['items'] as $item):
            $status = $item['status'];
            $id = $item['script']['update_id'];

            $color = match ($status) {
                'applied', 'included' => 'green',
                'pending', 'future' => 'yellow',
                default => 'red',
            };

            if ($status === 'pending'):
                $pending++;
            endif;

            echo $CLI->cout_color(strtoupper($status), $color)
                . "  {$id}" . PHP_EOL;
        endforeach;

        foreach ($plan['errors'] as $error):
            fwrite(STDERR, $CLI->cout_color($error, 'red') . PHP_EOL);
        endforeach;

        if ($plan['errors'] !== []):
            throw new \RuntimeException('Update plan is not safe to apply.');
        endif;

        echo "Pending core updates: {$pending}" . PHP_EOL;
        return $plan;
    }

    private function applyPending(PDO $pdo, CLI $CLI, array $plan): void
    {
        $applied = 0;

        foreach ($plan['items'] as $item):
            if ($item['status'] !== 'pending'):
                continue;
            endif;

            $script = $item['script'];
            $id = $script['update_id'];

            echo "Applying {$id}... ";

            try {
                $contents = file_get_contents($script['path']);

                if (
                    $contents === false
                    || hash('sha256', str_replace("\r\n", "\n", $contents))
                    !== $script['checksum_sha256']
                ):
                    throw new \RuntimeException('Script changed after planning.');
                endif;

                $operation = require $script['path'];

                if (!is_callable($operation)):
                    throw new \RuntimeException(
                        'Update script must return a callable.'
                    );
                endif;

                $operation($pdo);

                UpdateRegistry::recordScript(
                    $pdo,
                    $id,
                    $script['loom73_version'],
                    $script['checksum_sha256']
                );

                $applied++;
                echo $CLI->cout_color('OK', 'green') . PHP_EOL;
            } catch (\Throwable $e) {
                echo $CLI->cout_color('FAILED', 'red') . PHP_EOL;

                throw new \RuntimeException(
                    "Core update {$id} failed: {$e->getMessage()}",
                    0,
                    $e
                );
            }
        endforeach;

        echo "{$applied} core update(s) applied." . PHP_EOL;
    }
}