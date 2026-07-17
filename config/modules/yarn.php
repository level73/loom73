<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | Absolute path to Loom73's private storage directory.
    | This directory should live outside the public webroot.
    |
    | Example final upload path:
    |   {storage_path}/{upload_path}/2026/07/file.pdf
    |
    */

    'storage_path' => env(
        'YARN_STORAGE_PATH',
        dirname(__DIR__) . '/storage'
    ),

    /*
    |--------------------------------------------------------------------------
    | Upload Path
    |--------------------------------------------------------------------------
    |
    | Relative directory, inside storage_path, where uploaded assets
    | will be stored.
    |
    | This should not be a public URL path.
    |
    */

    'upload_path' => env('YARN_UPLOAD_PATH', 'uploads'),

    /*
    |--------------------------------------------------------------------------
    | Temporary Path
    |--------------------------------------------------------------------------
    |
    | Relative directory, inside storage_path, used for temporary files.
    | This can be used later for virus scanning, image processing,
    | document previews, import/export operations, or other transient files.
    |
    */

    'temp_path' => env('YARN_TEMP_PATH', 'tmp'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Upload Size
    |--------------------------------------------------------------------------
    |
    | Maximum accepted upload size in bytes.
    |
    | Note that PHP settings such as upload_max_filesize and post_max_size
    | must also allow this size, otherwise PHP will reject the upload before
    | Loom73 can validate it.
    |
    */

    'max_upload_size' => (int) env(
        'YARN_MAX_UPLOAD_SIZE',
        10 * 1024 * 1024
    ),

    /*
    |--------------------------------------------------------------------------
    | Default Visibility
    |--------------------------------------------------------------------------
    |
    | Default visibility assigned to newly uploaded assets.
    |
    | private:
    |   The asset is only accessible through permission checks.
    |
    | restricted:
    |   The asset is accessible through specific application rules,
    |   relations, signed links, or scoped permissions.
    |
    | public:
    |   The asset may be served without authentication, if public delivery
    |   is enabled.
    |
    */

    'default_visibility' => env('YARN_DEFAULT_VISIBILITY', 'private'),

    /*
    |--------------------------------------------------------------------------
    | Allowed Visibility Values
    |--------------------------------------------------------------------------
    |
    | Visibility values supported by Yarn.
    | These values should generally match the allowed values in the database.
    |
    */

    'allowed_visibility' => [
        'private',
        'restricted',
        'public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Public Delivery
    |--------------------------------------------------------------------------
    |
    | Controls whether assets marked as "public" can actually be delivered
    | without authentication.
    |
    | This allows a project to keep the "public" visibility value available
    | in the data model, while disabling public delivery globally.
    |
    */

    'public_delivery_enabled' => env('YARN_PUBLIC_DELIVERY_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Directory Strategy
    |--------------------------------------------------------------------------
    |
    | Determines how Yarn organizes uploaded files inside upload_path.
    |
    | date:
    |   uploads/YYYY/MM
    |
    | hash:
    |   uploads/ab/cd
    |
    | flat:
    |   uploads
    |
    | For the first Loom73 implementation, "date" is the recommended default.
    |
    */

    'directory_strategy' => env('YARN_DIRECTORY_STRATEGY', 'date'),

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    |
    | MIME allowlist for uploaded files.
    |
    | The array maps detected MIME types to allowed file extensions.
    | Yarn should detect MIME types server-side, for example through finfo,
    | and must never trust the browser-provided $_FILES['type'] value.
    |
    */

    'allowed_mimes' => [

        'image/jpeg' => [
            'jpg',
            'jpeg',
        ],

        'image/png' => [
            'png',
        ],

        'image/webp' => [
            'webp',
        ],

        'application/pdf' => [
            'pdf',
        ],

        'text/plain' => [
            'txt',
        ],

        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => [
            'docx',
        ],

        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => [
            'xlsx',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    |
    | Delivery rules for serving assets through the application.
    |
    | inline_mimes:
    |   MIME types that may be displayed inline in the browser.
    |
    | force_download_by_default:
    |   If true, Yarn will prefer Content-Disposition: attachment unless
    |   explicitly told otherwise.
    |
    */

    'delivery' => [

        'inline_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
            'text/plain',
        ],

        'force_download_by_default' => env(
            'YARN_FORCE_DOWNLOAD_BY_DEFAULT',
            false
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Naming
    |--------------------------------------------------------------------------
    |
    | Naming strategy for files stored on disk.
    |
    | stored_name_strategy:
    |   uuid:
    |     Generate a safe UUID-based filename and keep the original filename
    |     only as metadata in the database.
    |
    | preserve_original_name:
    |   Should normally remain false. Original filenames may contain unsafe
    |   characters, collisions, private information, or confusing extensions.
    |
    */

    'naming' => [

        'stored_name_strategy' => 'uuid',

        'preserve_original_name' => false,
    ],

];