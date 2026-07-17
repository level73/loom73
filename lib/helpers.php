<?php
/** Bypass missing env function */
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $_SERVER)) {
            return $default;
        }

        $value = $_SERVER[$key];

        if ($value === null || $value === '') {
            return $default;
        }

        $normalized = strtolower(trim((string) $value));

        return match ($normalized) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}