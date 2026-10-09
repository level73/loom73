<?php

namespace Loom73\Gauge;
use RuntimeException;

final class Activation
{
    public static function path(): string
    {
        return ROOT_DIR .
            DIRECTORY_SEPARATOR . 'storage' .
            DIRECTORY_SEPARATOR . '.loom73-gauge-enabled';
    }

    /** Get current state of Gauge */
    public static function state(): ?array
    {
        $path = self::path();
        if(!is_file($path) || !is_readable($path) || is_link($path)):
            return null;
        endif;
        $contents = @file_get_contents($path);
        if($contents === false):
            return null;
        endif;
        $state = json_decode($contents, true);

        if(!is_array($state) ||
            !is_string($state['activation_id'] ?? null) ||
            preg_match('/\A[a-f0-9]{64}\z/', $state['activation_id']) !== 1 ||
            !is_bool($state['allow_production'] ?? null)):
            return null;
        endif;

        return [
            'activation_id' => $state['activation_id'],
            'allow_production' => $state['allow_production'],
        ];
    }

    /** Check if Gauge is enabled */
    public static function enabled(): bool
    {
        $state = self::state();

        if($state === null):
            return false;
        endif;

        return match($_SERVER['SYSTEM_STATUS'] ?? ''){
            'development' => true,
            'production'  => $state['allow_production'],
            default => false,
        };
    }

    public static function enable(bool $allowProduction = false): bool
    {
        $environment = $_SERVER['SYSTEM_STATUS'] ?? '';

        if(!in_array($environment, ['development', 'production'], true)):
            throw new RuntimeException("Gauge cannot be enabled in environment: {$environment}");
        endif;

        if($environment === 'production' && !$allowProduction):
            throw new RuntimeException("Use --production to enable Gauge in production");
        endif;

        if($environment !== 'production' && $allowProduction):
            throw new RuntimeException("--production is only valid in production");
        endif;

        $path = self::path();
        if(is_link($path)):
            throw new RuntimeException("Gauge marker cannot be a symbolic link ");
        endif;

        $existing = self::state();
        if(is_file($path) && $existing === null):
            throw new RuntimeException("Gauge marker is invalid. Disable it before enabling Gauge");
        endif;

        if($existing !== null && self::enabled()):
            return false;
        endif;

        $directory = dirname($path);
        if(!is_dir($directory) || !is_writable($directory)):
            throw new RuntimeException("Gauge storage directory is not writable");
        endif;

        $state = [
            'activation_id' => bin2hex(random_bytes(32)),
            'allow_production' => $environment === 'production'
        ];
        $contents = json_encode($state, JSON_THROW_ON_ERROR) . PHP_EOL;
        $written = @file_put_contents($path, $contents, LOCK_EX);

        if($written !== strlen($contents)):
            throw new RuntimeException("Unable to write the Gauge marker");
        endif;

        return true;
    }


    public static function disable(): bool
    {
        $path = self::path();

        if (!file_exists($path) && !is_link($path)):
            return false;
        endif;

        if (!is_file($path) && !is_link($path)):
            throw new RuntimeException('Gauge marker path is not a file.');
        endif;

        if (!@unlink($path)):
            throw new RuntimeException('Unable to remove Gauge marker.');
        endif;

        return true;
    }
}