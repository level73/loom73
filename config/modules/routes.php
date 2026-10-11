<?php

return [
    'core' => [
        'api'   => \Loom73\Weave\Controllers\Core\ApiCtrl::class,
        'asset' => \Loom73\Weave\Controllers\Core\AssetCtrl::class,
        'gauge' => \Loom73\Weave\Controllers\Core\GaugeCtrl::class,
        'user'  => \Loom73\Weave\Controllers\Core\UserCtrl::class,
    ],
    'overrides' => []
];