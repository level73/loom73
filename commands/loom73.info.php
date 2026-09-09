<?php

namespace Loom73\Shuttle;

use Composer\InstalledVersions;
use Dotenv\Dotenv;
use Loom73\Beam\Connection;
use Loom73\Beam\QueryResult;
use Loom73\Woodframe\Config;

class Loom73Info
{
    private string $root;
    private CLI $CLI;

    private bool $hasErrors = false;
    private bool $hasWarnings = false;


    public function __construct()
    {
        $this->root = dirname(__DIR__);
        $this->CLI = new CLI();

        $this->run();
    }


    private function run(): void
    {
        $package = $this->readJson('package.json');
        $composer = $this->readJson('composer.json');

        $this->heading('Loom73');

        $this->line(
            'Version',
            $package['version'] ?? 'unknown'
        );

        $this->line(
            'Environment',
            $_SERVER['SYSTEM_STATUS'] ?? 'unknown'
        );

        $this->line(
            'PHP',
            PHP_VERSION
        );

        echo PHP_EOL;

        $this->heading('Application health');

        $this->checkEnvironment();
        $this->checkConfiguration();
        $this->checkDatabase();

        $this->checkDirectory(
            'storage/',
            $this->root . '/storage'
        );

        $this->checkDirectory(
            'storage/uploads/',
            $this->root . '/storage/uploads'
        );

        $this->checkDirectory(
            'storage/logs/',
            $this->root . '/storage/logs'
        );

        $this->checkDirectory(
            'storage/cache/',
            $this->root . '/storage/cache'
        );

        echo PHP_EOL;

        $this->heading('Runtime requirements');

        $this->showRuntimeRequirements(
            $composer['require'] ?? []
        );

        echo PHP_EOL;

        $this->heading('Dependencies');

        $this->showDependency('vlucas/phpdotenv');
        $this->showDependency('phpmailer/phpmailer');

        echo PHP_EOL;

        $this->heading('Components');

        $this->showConfigState(
            'Ledger',
            'ledger.enabled'
        );

        $this->showConfigState(
            'Gauge',
            'gauge.enabled'
        );

        echo PHP_EOL;

        $this->showOverallStatus();

        exit($this->hasErrors ? 1 : 0);
    }


    /*
     * Environment
     */

    private function checkEnvironment(): void
    {
        $example = $this->root . '/config/example.env';
        $actual = $this->root . '/config/.env';

        if (!is_file($actual)):
            $this->status(
                'Environment',
                '.env missing',
                'error'
            );

            return;
        endif;

        if (!is_file($example)):
            $this->status(
                'Environment',
                '.env loaded',
                'warning',
                'example.env not found'
            );

            return;
        endif;

        $expected = Dotenv::parse(
            file_get_contents($example)
        );

        $missing = [];

        foreach (array_keys($expected) as $key):
            if (
                !array_key_exists($key, $_SERVER)
                || $_SERVER[$key] === ''
            ):
                $missing[] = $key;
            endif;
        endforeach;

        if ($missing !== []):
            $this->status(
                'Environment',
                'loaded',
                'warning',
                sprintf(
                    '%d value(s) missing: %s',
                    count($missing),
                    implode(', ', $missing)
                )
            );

            return;
        endif;

        $this->status(
            'Environment',
            'loaded',
            'ok',
            sprintf(
                '%d values',
                count($expected)
            )
        );
    }


    /*
     * Woodframe\Config
     */

    private function checkConfiguration(): void
    {
        $config = Config::all();

        if ($config === []):
            $this->status(
                'Configuration',
                'not loaded',
                'error'
            );

            return;
        endif;

        $this->status(
            'Configuration',
            'loaded',
            'ok',
            sprintf(
                '%d groups: %s',
                count($config),
                implode(', ', array_keys($config))
            )
        );
    }


    /*
     * Beam
     */

    private function checkDatabase(): void
    {
        $Connection = new Connection();

        if (!$Connection->ping()):
            $this->status(
                'Database',
                'unavailable',
                'error'
            );

            return;
        endif;

        $this->status(
            'Database',
            'connected',
            'ok'
        );
    }

    /*
     * Runtime filesystem
     */

    private function checkDirectory(
        string $label,
        string $path
    ): void {
        if (!is_dir($path)):
            $this->status(
                $label,
                'missing',
                'error'
            );

            return;
        endif;

        if (!is_writable($path)):
            $this->status(
                $label,
                'not writable',
                'error'
            );

            return;
        endif;

        $this->status(
            $label,
            'writable',
            'ok'
        );
    }


    /*
     * Composer runtime requirements
     */

    private function showRuntimeRequirements(
        array $requirements
    ): void {
        foreach ($requirements as $requirement => $constraint):

            if ($requirement === 'php'):
                $this->line(
                    'PHP',
                    sprintf(
                        '%s  [required %s]',
                        PHP_VERSION,
                        $constraint
                    )
                );

                continue;
            endif;

            if (!str_starts_with($requirement, 'ext-')):
                continue;
            endif;

            $extension = substr(
                $requirement,
                4
            );

            if (extension_loaded($extension)):
                $this->status(
                    $requirement,
                    'loaded',
                    'ok'
                );
            else:
                $this->status(
                    $requirement,
                    'missing',
                    'error'
                );
            endif;

        endforeach;
    }


    /*
     * Composer installed packages
     */

    private function showDependency(
        string $package
    ): void {
        if (!InstalledVersions::isInstalled($package)):
            $this->status(
                $package,
                'missing',
                'error'
            );

            return;
        endif;

        $version = InstalledVersions::getPrettyVersion(
            $package
        );

        $this->status(
            $package,
            $version ?? 'installed',
            'ok'
        );
    }


    /*
     * Config-backed component state
     */

    private function showConfigState(
        string $label,
        string $key
    ): void {
        if (!Config::has($key)):
            $this->line(
                $label,
                'not configured'
            );

            return;
        endif;

        $enabled = (bool) Config::get(
            $key,
            false
        );

        $this->line(
            $label,
            $enabled ? 'enabled' : 'disabled'
        );
    }


    /*
     * Output
     */

    private function heading(string $label): void
    {
        echo $this->CLI->cout_color($label, 'cyan') . PHP_EOL;
        echo $this->CLI->cout_color(
                str_repeat('-', strlen($label)),
                'dark grey'
            ) . PHP_EOL;
    }


    private function line(
        string $label,
        string $value
    ): void {
        printf(
            "  %-24s %s%s",
            $label,
            $value,
            PHP_EOL
        );
    }


    private function status(
        string $label,
        string $value,
        string $status,
        ?string $detail = null
    ): void {
        [$symbol, $color] = match ($status) {
            'ok'      => ['✓', 'green'],
            'warning' => ['!', 'yellow'],
            'error'   => ['✗', 'red'],
            default   => ['·', 'cyan'],
        };

        if ($status === 'warning'):
            $this->hasWarnings = true;
        endif;

        if ($status === 'error'):
            $this->hasErrors = true;
        endif;

        $output = $this->CLI->cout_color(
            sprintf('%s %s', $symbol, $value),
            $color
        );

        if ($detail !== null):
            $output .= $this->CLI->cout_color(
                sprintf('  (%s)', $detail),
                'dark grey'
            );
        endif;

        $this->line($label, $output);
    }


    private function showOverallStatus(): void
    {
        if ($this->hasErrors):
            $value = 'UNHEALTHY';
            $color = 'red';
        elseif ($this->hasWarnings):
            $value = 'HEALTHY WITH WARNINGS';
            $color = 'yellow';
        else:
            $value = 'HEALTHY';
            $color = 'green';
        endif;

        $this->line(
            'Status',
            $this->CLI->cout_color($value, $color)
        );
    }


    /*
     * Files
     */

    private function readJson(
        string $file
    ): array {
        $path = $this->root
            . DIRECTORY_SEPARATOR
            . $file;

        if (!is_file($path)):
            return [];
        endif;

        $content = file_get_contents($path);

        if ($content === false):
            return [];
        endif;

        $decoded = json_decode(
            $content,
            true
        );

        return is_array($decoded)
            ? $decoded
            : [];
    }
}