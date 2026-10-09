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

/** Get AppName or return null */
function appName(): ?string
{
    $app = $_SERVER['APPNAME'] ?? null;
    return is_string($app) && $app !== '' ? $app : null;
}

function codeName(): ?string
{
    $codename = $_SERVER['CODENAME'] ?? null;
    return is_string($codename) && $codename !== '' ? $codename : null;
}