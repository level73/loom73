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

        if (!is_dir($path)) :
            throw new RuntimeException("Config directory not found: {$path}");
        endif;

        foreach (glob($path . DIRECTORY_SEPARATOR . '*.php') as $file) :
            self::loadFile($file);
        endforeach;
    }

    /**
     * Load a single PHP config file.
     */
    public static function loadFile(string $file): void
    {
        if (!is_file($file)) :
            throw new RuntimeException("Config file not found: {$file}");
        endif;

        $key = basename($file, '.php');
        $config = require $file;

        if (!is_array($config)) :
            throw new RuntimeException("Config file must return an array: {$file}");
        endif;

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
        if ($key === '') :
            return self::$items;
        endif;

        $segments = self::parsePath($key);
        $value = self::$items;

        foreach ($segments as $segment) :
            if (!is_array($value) || !array_key_exists($segment, $value)) :
                return $default;
            endif;

            $value = $value[$segment];
        endforeach;

        return $value;
    }

    /**
     * Set a config value using dot notation.
     *
     * Useful for tests or runtime overrides.
     */
    public static function set(string $key, mixed $value): void
    {
        $segments = self::parsePath($key);
        $items = &self::$items;

        foreach ($segments as $segment) :
            if (!isset($items[$segment]) || !is_array($items[$segment])) :
                $items[$segment] = [];
            endif;

            $items = &$items[$segment];
        endforeach;

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

    /**
     * Parse a key allowing for dots in it to be
     * treated as strings. Useful for MIME types
     * that contain dots.
     * @param string $key
     * @return array
     */
    private static function parsePath(string $key): array
    {
        if (!preg_match(
            '/\A(?:[^.{}]+|\{[^{}]+\})(?:\.(?:[^.{}]+|\{[^{}]+\}))*\z/',
            $key
        )) :
            throw new RuntimeException("Invalid config path: {$key}");
        endif;

        preg_match_all(
            '/\{([^{}]+)\}|([^.{}]+)/',
            $key,
            $matches,
            PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL
        );

        return array_map(
            static fn(array $part): string => $part[1] ?? $part[2],
            $matches
        );
    }

    public static function loadOverrides(string $path): void
    {
        if (!file_exists($path)) :
            return;
        endif;

        if (!is_dir($path)) :
            throw new RuntimeException("Config override path is not a directory: {$path}");
        endif;

        $files = glob($path . DIRECTORY_SEPARATOR . '*.php');

        if ($files === false) :
            throw new RuntimeException("Cannot read config overrides: {$path}");
        endif;

        foreach ($files as $file) :
            if (!is_file($file)) :
                throw new RuntimeException("Invalid config override: {$file}");
            endif;

            $override = require $file;

            if (!is_array($override)) :
                throw new RuntimeException("Config override must return an array: {$file}");
            endif;

            $module = basename($file, '.php');

            foreach ($override as $key => $value):
                if (!is_string($key)) :
                    throw new RuntimeException(
                        "Config override keys must be strings: {$file}"
                    );
                endif;

                self::set($module . '.' . $key, $value);
            endforeach;
        endforeach;
    }



}