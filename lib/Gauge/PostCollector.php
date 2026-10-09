<?php

namespace Loom73\Gauge;

final class PostCollector
{
    private const string KEY = 'gauge_redirect_snapshot';

    public static function save(): void
    {
        $app = \codeName();

        if (
            !is_string($app)
            || $app === ''
            || session_status() !== PHP_SESSION_ACTIVE
            || !OptIn::requested()
        ):
            return;
        endif;

        $snapshot = Collector::snapshot();
        $state = Activation::state();

        if (
            $snapshot === null
            || ($snapshot['method'] ?? null) !== 'POST'
            || $state === null
        ):
            return;
        endif;

        $_SESSION[$app][self::KEY] = [
            'activation_id' => $state['activation_id'],
            'expires_at' => time() + 120,
            'snapshot' => $snapshot,
        ];
    }

    public static function take(): ?array
    {
        $app = \codeName();

        if (
            !is_string($app)
            || $app === ''
            || session_status() !== PHP_SESSION_ACTIVE
        ):
            return null;
        endif;

        $entry = $_SESSION[$app][self::KEY] ?? null;
        unset($_SESSION[$app][self::KEY]);

        $state = Activation::state();

        if (
            !is_array($entry)
            || !is_array($entry['snapshot'] ?? null)
            || !is_string($entry['activation_id'] ?? null)
            || !is_int($entry['expires_at'] ?? null)
            || time() > $entry['expires_at']
            || !OptIn::requested()
            || $state === null
            || !hash_equals($state['activation_id'], $entry['activation_id'])
        ):
            return null;
        endif;

        return $entry['snapshot'];
    }

    public static function discard(): void
    {
        $app = \codeName();

        if (
            is_string($app)
            && $app !== ''
            && session_status() === PHP_SESSION_ACTIVE
        ):
            unset($_SESSION[$app][self::KEY]);
        endif;
    }
}