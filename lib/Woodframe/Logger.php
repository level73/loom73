<?php

namespace Loom73\Woodframe;
class Logger
    {
    public static function error(
        string $source,
        string $message,
        array $context = []
        ): void
    {
        $trace = debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS,
            2
        );

        $caller = $trace[0] ?? [];

        $entry = sprintf(
            '[Loom73][%s] %s',
            $source,
            $message
        );

        if (isset($caller['file'])):
            $entry .= sprintf(
                ' [%s:%s]',
                $caller['file'],
                $caller['line'] ?? '?'
            );
        endif;

        if ($context !== []):
            $encodedContext = json_encode(
                                $context,
                                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                            );
            if ($encodedContext !== false):
                $entry .= ' ' . $encodedContext;
            endif;
        endif;

        error_log($entry);
    }
}