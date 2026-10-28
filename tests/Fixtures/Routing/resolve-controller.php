<?php

declare(strict_types=1);

use Loom73\Woodframe\Config;

// ROOT_DIR belongs to the fixture, source files belong to the project
$sourceRoot = dirname(__DIR__, 3);
define('ROOT_DIR', __DIR__ . '/instance');

require_once $sourceRoot . '/lib/Woodframe/Config.php';
require_once $sourceRoot . '/lib/loom73.php';

Config::set(
    'routes',
    json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR)
);

echo resolveController($argv[2], $argv[3]);
