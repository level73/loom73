<?php

namespace Loom73\Woodframe;

class Flash
{
    private const string KEY = 'FLASH';

    public static function add(string $type, array $message): void
    {
        $app = \codeName();
        $_SESSION[$app][self::KEY][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    public static function success(array $message): void
    {
        self::add('success', $message);
    }

    public static function error(array $message): void
    {
        self::add('error', $message);
    }

    public static function warning(array $message): void
    {
        self::add('warning', $message);
    }

    public static function info(array $message): void
    {
        self::add('info', $message);
    }

    public static function all(): array
    {
        $app = \codeName();
        $messages = $_SESSION[$app][self::KEY] ?? [];
        unset($_SESSION[$app][self::KEY]);

        return $messages;
    }

}