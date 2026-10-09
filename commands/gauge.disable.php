<?php

namespace Loom73\Shuttle;
use Loom73\Gauge\Activation;
final class GaugeDisable
{

    public function __construct(array $args = [])
    {
        if ($args !== []):
            fwrite(STDERR, "Usage: php shuttle gauge.disable" . PHP_EOL);
            exit(2);
        endif;

        $CLI = new CLI();

        try {
            $changed = Activation::disable();
        } catch (\Throwable $e) {
            fwrite(STDERR, $CLI->cout_color($e->getMessage(), 'red') . PHP_EOL);
            exit(1);
        }

        $message = $changed
            ? 'Gauge disabled for this instance.'
            : 'Gauge is already disabled for this instance.';

        echo $CLI->cout_color($message, 'green') . PHP_EOL;
    }
}