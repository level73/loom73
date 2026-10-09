<?php

namespace Loom73\Gauge;

final class OptIn
{
    private const string SESSION_KEY = 'gauge_activation_id';

    /**
     * Call only after auth and CSRF verification
     */
    public static function optIn(): bool
    {
        $app = \codeName();
        $state = Activation::state();

        if (
            $app === null
            || session_status() !== PHP_SESSION_ACTIVE
            || !Activation::enabled()
            || $state === null
        ):
            return false;
        endif;

        if (!isset($_SESSION[$app]) || !is_array($_SESSION[$app])):
            $_SESSION[$app] = [];
        endif;

        $_SESSION[$app][self::SESSION_KEY] = $state['activation_id'];

        return true;
    }

    public static function optOut(): void
    {
        $app = \codeName();

        if (
            $app !== null
            && session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION[$app])
            && is_array($_SESSION[$app])
        ):
            unset($_SESSION[$app][self::SESSION_KEY]);
            PostCollector::discard();
        endif;
    }

    /**
     * Indicates the browser request to collect information, not the user's authorization
     * User auth/ability must be checked before output
     */
    public static function requested(): bool
    {
        $app = \codeName();

        if (
            $app === null
            || session_status() !== PHP_SESSION_ACTIVE
            || !Activation::enabled()
        ):
            return false;
        endif;

        $state = Activation::state();
        $stored = $_SESSION[$app][self::SESSION_KEY] ?? null;

        return $state !== null
            && is_string($stored)
            && hash_equals($state['activation_id'], $stored);
    }


}