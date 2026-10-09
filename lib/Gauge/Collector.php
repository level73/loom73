<?php

namespace Loom73\Gauge;

final class Collector
{
    private static ?float $startedAt = null;
    private static int $queryCount = 0;
    private static float $querySeconds = 0.0;
    private static string $method = 'GET';
    private static string $path = '/';

    public static function start(): void
    {
        if(self::$startedAt !== null) :
            return;
        endif;

        self::$startedAt = (float)($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));

        self::$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $path = parse_url(
            (string) ($_SERVER['REQUEST_URI'] ?? '/'),
            PHP_URL_PATH
        );

        $segments = array_values(array_filter(
            explode('/', trim(is_string($path) ? $path : '/', '/')),
            static fn (string $segment): bool => $segment !== ''
        ));

        if (count($segments) > 2):
            $segments = array_merge(
                array_slice($segments, 0, 2),
                array_fill(0, count($segments) - 2, '{param}')
            );
        endif;

        self::$path = '/' . implode('/', $segments);
    }

    public static function active(): bool
    {
        return self::$startedAt !== null;
    }

    public static function recordQuery(float $seconds): void
    {
        if(!self::active()):
            return;
        endif;

        self::$queryCount++;
        self::$querySeconds += max(0.0, $seconds);
    }

    public static function snapshot(): ?array
    {
        if(self::$startedAt === null):
            return null;
        endif;

        return [
            'method'            => self::$method,
            'path'              => self::$path,
            'request_ms'        => (microtime(true) - self::$startedAt) * 1000,
            'memory_bytes'      => memory_get_usage(),
            'peak_memory_bytes' => memory_get_peak_usage(),
            'query_count'       => self::$queryCount,
            'query_ms'          => self::$querySeconds * 1000,
            'php_version'       => PHP_VERSION,
            'http_status'       => http_response_code() ?: 200
        ];
    }
}