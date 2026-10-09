<?php

namespace Loom73\Shuttle;
use Loom73\Gauge\Activation;
final class GaugeEnable
{
    public function __construct(array $args = [])
    {
        $CLI = new CLI();

        if(array_diff($args, ['--production']) !== []):
            fwrite(STDERR, "Usage: php shuttle gauge.enable [--production]" . PHP_EOL);
            exit(2);
        endif;

        try {
            $changed = Activation::enable(
                in_array('--production', $args, true),
            );
        } catch (\Throwable $e) {
            fwrite(STDERR, $CLI->cout_color($e->getMessage(), 'red') . PHP_EOL);
            exit(1);
        }

        $message = $changed
            ? 'Gauge enabled for this instance. Browser opt-in is still required.'
            : 'Gauge is already enabled for this instance.';

        echo $CLI->cout_color($message, 'green') . PHP_EOL;

    }
}