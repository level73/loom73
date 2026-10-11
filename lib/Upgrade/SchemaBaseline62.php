<?php

namespace Loom73\Upgrade;

final class SchemaBaseline62
{
    public const string VERSION = '6.2.0';

    public const array REQUIRED_COLUMNS = [
        'auth_role' => [
            'idauth_role', 'role',
        ],
        'auth_user' => [
            'idauth_user', 'username', 'email', 'password', 'salt',
            'role', 'status', 'recovery', 'recovery_created_at',
            'modified_at', 'created_at',
        ],
        'auth_session' => [
            'idauth_session', 'session', 'auth_user',
            'created_at', 'modified_at',
        ],
        'asset_types' => [
            'idasset_type', 'slug', 'label', 'description',
            'is_active', 'modified_at', 'created_at',
        ],
        'assets' => [
            'idasset', 'uuid', 'owner_type', 'owner_id', 'owner_slot',
            'asset_type', 'original_name', 'stored_name', 'disk_path',
            'mime_type', 'extension', 'size_bytes', 'checksum_sha256',
            'visibility', 'status', 'uploaded_by', 'modified_at',
            'created_at',
        ],
        'ledger_events' => [
            'idledger_event', 'actor_user', 'action', 'owner_type',
            'owner_id', 'summary', 'metadata', 'ip_address',
            'user_agent', 'created_at',
        ],
    ];

    public const array PRIMARY_KEYS = [
        'auth_role' => 'idauth_role',
        'auth_user' => 'idauth_user',
        'auth_session' => 'idauth_session',
        'asset_types' => 'idasset_type',
        'assets' => 'idasset',
        'ledger_events' => 'idledger_event',
    ];

    public const array UNIQUE_COLUMNS = [
        'auth_user' => ['email'],
        'asset_types' => ['slug'],
        'assets' => ['uuid'],
    ];

    public const array FOREIGN_KEYS = [
        'auth_user.role' => 'auth_role.idauth_role',
        'auth_session.auth_user' => 'auth_user.idauth_user',
        'assets.asset_type' => 'asset_types.idasset_type',
        'assets.uploaded_by' => 'auth_user.idauth_user',
        'ledger_events.actor_user' => 'auth_user.idauth_user',
    ];
}