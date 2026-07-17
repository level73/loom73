<?php

use Loom73\Beam\User;
use Loom73\Yarn\Asset;

return [
    'user' => [
        'label' => 'User',
        'model' => User::class,
        'primary_key' => 'idauth_user',

        'assets' => [
            'avatar' => [
                'label' => 'Avatar',
                'multiple' => false,
                'replace_existing' => true,
                'allowed_asset_types' => ['image_avatar'],
                'default_asset_type' => 'image_avatar',
                'default_visibility' => Asset::VISIBILITY_RESTRICTED,
            ],
        ],
    ],
];