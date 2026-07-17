<?php

namespace Loom73\Woodframe;
use RuntimeException;
use stdClass;

class Config
{
    protected static array $items = [];

    /**
     * Load all PHP config files from a directory.
     *
     * Each file must return an array.
     * Example:
     *   config/modules/yarn.php becomes Config::get('yarn')
     */
    public static function load(string $path): void
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!is_dir($path)) {
            throw new RuntimeException("Config directory not found: {$path}");
        }

        foreach (glob($path . DIRECTORY_SEPARATOR . '*.php') as $file) {
            self::loadFile($file);
        }
    }

    /**
     * Load a single PHP config file.
     */
    public static function loadFile(string $file): void
    {
        if (!is_file($file)) {
            throw new RuntimeException("Config file not found: {$file}");
        }

        $key = basename($file, '.php');
        $config = require $file;

        if (!is_array($config)) {
            throw new RuntimeException("Config file must return an array: {$file}");
        }

        self::$items[$key] = $config;
    }

    /**
     * Get a config value using dot notation.
     *
     * Example:
     *   Config::get('yarn.storage_path')
     *   Config::get('yarn.uploads.max_size', 10485760)
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return self::$items;
        }

        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set a config value using dot notation.
     *
     * Useful for tests or runtime overrides.
     */
    public static function set(string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $items = &self::$items;

        foreach ($segments as $segment) {
            if (!isset($items[$segment]) || !is_array($items[$segment])) {
                $items[$segment] = [];
            }

            $items = &$items[$segment];
        }

        $items = $value;
    }

    /**
     * Check if a config key exists.
     */
    public static function has(string $key): bool
    {
        $sentinel = new stdClass();

        return self::get($key, $sentinel) !== $sentinel;
    }

    /**
     * Return all loaded config.
     */
    public static function all(): array
    {
        return self::$items;
    }

    /**
     * Clear loaded config.
     * Useful for tests, CLI scripts, or re-bootstrap.
     */
    public static function clear(): void
    {
        self::$items = [];
    }

}