<?php

namespace Loom73\Shuttle;
use Loom73\Gauge\Activation;
final class GaugeInfo
{

    public function __construct(array $args = [])
    {
        if ($args !== []):
            fwrite(STDERR, "Usage: php shuttle gauge.info" . PHP_EOL);
            exit(2);
        endif;

        $CLI = new CLI();
        $environment = $_SERVER['SYSTEM_STATUS'] ?? 'unknown';
        $path = Activation::path();
        $exists = file_exists($path) || is_link($path);
        $state = Activation::state();

        echo "Environment: {$environment}" . PHP_EOL;

        if (!$exists):
            echo $CLI->cout_color('Marker: absent; Gauge disabled.', 'yellow') . PHP_EOL;
            return;
        endif;

        if ($state === null):
            fwrite(STDERR, $CLI->cout_color('Marker: invalid or unreadable; Gauge disabled.', 'red') . PHP_EOL);
            exit(1);
        endif;

        echo 'Marker: valid' . PHP_EOL;
        echo 'Production access: '
            . ($state['allow_production'] ? 'allowed' : 'not allowed')
            . PHP_EOL;

        if (!Activation::enabled()):
            echo $CLI->cout_color('Gauge inactive in this environment.', 'yellow') . PHP_EOL;
            return;
        endif;

        echo $CLI->cout_color(
                'Gauge enabled for this instance; browser opt-in required.',
                'green'
            ) . PHP_EOL;
    }

}