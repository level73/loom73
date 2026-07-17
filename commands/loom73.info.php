<?php
namespace Loom73\Shuttle;

use Shuttle;

class Loom73Info extends Shuttle {
    public function __construct(?array $args) {
        parent::__construct();
        $cli = new CLI();
        echo 'Shuttle version: ' . $cli->cout_color(self::$version, 'green') . PHP_EOL;
        echo 'Loom73 version: ' . $cli->cout_color(LOOM73['version'], 'green') . PHP_EOL;
    }
}