<?php

namespace Loom73\Woodframe;
class Logger
{
    public static function error(
        string $source,
        string $message,
        array $context = []
    ): void {
        $origin = self::origin();

        $entry = sprintf(
            "[%s] [ERROR] [%s] %s [%s:%s]",
            date('Y-m-d H:i:s'),
            $source,
            $message,
            $origin['file'] ?? 'unknown',
            $origin['line'] ?? '?'
        );

        if ($context !== []):
            $encodedContext = json_encode(
                $context,
                JSON_UNESCAPED_SLASHES |
                JSON_UNESCAPED_UNICODE
            );

            if ($encodedContext !== false):
                $entry .= ' ' . $encodedContext;
            endif;
        endif;

        self::write($entry);
    }

    protected static function write(string $entry): void
    {
        $logFile = ROOT_DIR . '/storage/logs/loom73.log';

        $written = @file_put_contents(
            $logFile,
            $entry . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );

        if ($written === false):
            error_log(
                '[Loom73][Logger] Unable to write to Loom73 log. '
                . $entry
            );
        endif;
    }

    protected static function origin(): array
    {
        $trace = debug_backtrace(
            DEBUG_BACKTRACE_IGNORE_ARGS
        );

        foreach ($trace as $frame):
            if (
                isset($frame['class']) &&
                $frame['class'] === self::class
            ):
                continue;
            endif;

            if (isset($frame['file'])):
                return [
                    'file' => $frame['file'],
                    'line' => $frame['line'] ?? null,
                ];
            endif;
        endforeach;

        return [];
    }
}