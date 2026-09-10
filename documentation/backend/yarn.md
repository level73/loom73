# Yarn

Yarn manages uploaded files as application resources.

It validates file content, stores files outside the public web root, records metadata, applies owner-slot policy and delivers authorized files through application routes.

```text
uploaded file
    ↓
owner-slot policy
    ↓
server-side validation
    ↓
private storage
    ↓
database registration
    ↓
optional replacement
    ↓
controlled delivery
```

## Concepts

An asset has four related classifications:

```text
owner_type
    logical entity that owns the asset

owner_id
    identifier of the owning record

owner_slot
    position or purpose within that owner

asset_type
    semantic file classification
```

Example:

```text
owner_type = user
owner_id = 12
owner_slot = avatar
asset_type = image_avatar
```

The user who uploaded the file is stored separately as `uploaded_by`.

## Class index

| Class | Responsibility |
| --- | --- |
| `AssetUploader` | Coordinate the complete upload operation. |
| `AssetPolicy` | Resolve owner, slot, type, visibility and replacement rules. |
| `AssetValidator` | Validate size, MIME type, extension and upload state. |
| `AssetValidationResult` | Describe validation success and detected metadata. |
| `AssetStorage` | Name, store, locate and delete physical files. |
| `Asset` | Register and retrieve asset database records. |
| `AssetType` | Resolve semantic asset types. |
| `AssetUploadResult` | Describe the complete upload outcome. |
| `AssetDelivery` | Emit inline or attachment responses. |

## Configuration

Yarn reads:

```text
config/modules/yarn.php
```

Important environment variables include:

```env
YARN_STORAGE_PATH='/var/www/example.com/storage'
YARN_UPLOAD_PATH='uploads'
YARN_TEMP_PATH='tmp'
YARN_MAX_UPLOAD_SIZE=10485760
YARN_DEFAULT_VISIBILITY='private'
YARN_DIRECTORY_STRATEGY='date'
YARN_PUBLIC_DELIVERY_ENABLED=false
YARN_FORCE_DOWNLOAD_BY_DEFAULT=false
```

The storage path must be absolute and outside:

```text
public_html/public
```

PHP must also permit the configured upload size through `upload_max_filesize` and `post_max_size`.

## Owner registry

Owner and slot rules live in:

```text
config/modules/owners.php
```

Example:

```php
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
                'allowed_asset_types' => [
                    'image_avatar',
                ],
                'default_asset_type' => 'image_avatar',
                'default_visibility' =>
                    Asset::VISIBILITY_RESTRICTED,
            ],
        ],
    ],
];
```

Owner types and slots are application vocabulary. Add them deliberately rather than accepting arbitrary request values.

Asset types are database records. The baseline installation creates:

```text
image_avatar
```

Additional types can be created with:

```console
php shuttle asset_type.new
```

## Uploading a file

The smallest controller-level upload is:

```php
$Uploader = new AssetUploader();

$result = $Uploader->upload(
    file: $_FILES['avatar'],
    ownerType: 'user',
    ownerId: (string) $userId,
    ownerSlot: 'avatar',
    uploadedBy: $userId
);

if ($result->fails()):
    $this->redirectWithError('/user/profile', [
        'message' => 'The avatar could not be uploaded.',
        'data' => null,
        'error' => ($_SERVER['DEBUG'] ?? 0) > 0
            ? $result->errorMessage()
            : null,
    ]);
endif;
```

The base controller also provides `uploadAsset()` and `uploadAssets()` wrappers.

Always supply `ownerType`, `ownerId` and `ownerSlot`. Policy resolution depends on them even though some compatibility signatures still declare nullable arguments.

## AssetPolicy

`AssetPolicy::resolve()` validates and resolves:

```text
owner type
owner slot
asset-type slug and database ID
visibility
multiple flag
replacement behavior
```

Example:

```php
$policy = $Policy->resolve(
    ownerType: 'user',
    ownerSlot: 'avatar'
);
```

Slot defaults are used when asset type, visibility or replacement are omitted.

Unknown owners, slots and asset types throw `InvalidArgumentException`. `AssetUploader` catches policy exceptions and returns a failed `AssetUploadResult`.

Supported visibility values are:

```php
Asset::VISIBILITY_PRIVATE;
Asset::VISIBILITY_RESTRICTED;
Asset::VISIBILITY_PUBLIC;
```

A slot can restrict visibility with:

```php
'allowed_visibility' => [
    Asset::VISIBILITY_PRIVATE,
    Asset::VISIBILITY_RESTRICTED,
],
```

## AssetValidator

The validator checks:

```text
PHP upload error
original filename
temporary path
HTTP-upload origin
actual file size
maximum size
empty files
filename extension
server-detected MIME type
extension permitted for that MIME type
```

MIME detection uses Fileinfo:

```php
finfo_open(FILEINFO_MIME_TYPE);
```

The browser-supplied `$_FILES['type']` value is not trusted.

Default allowed combinations include:

```text
image/jpeg   → jpg, jpeg
image/png    → png
image/webp   → webp
application/pdf → pdf
text/plain   → txt
DOCX MIME    → docx
XLSX MIME    → xlsx
```

Validate an HTTP upload with:

```php
$Validator = new AssetValidator(
    Config::get('yarn')
);

$validation = $Validator->validateUploadedFile(
    $_FILES['document']
);
```

Validate an existing local file with:

```php
$validation = $Validator->validateLocalFile(
    '/temporary/report.pdf',
    'report.pdf'
);
```

`validateLocalFile()` is useful for generated files, conversions and imports.

### AssetValidationResult

Readonly properties are:

```php
$validation->valid;
$validation->errors;
$validation->originalName;
$validation->extension;
$validation->mimeType;
$validation->sizeBytes;
```

Helper methods are:

```php
$validation->passes();
$validation->fails();
$validation->firstError();
```

Validation errors are technical English messages. Applications may translate or replace them before displaying them to a user.

## AssetStorage

AssetStorage owns physical files.

### Store an HTTP upload

```php
$stored = $Storage->storeUploadedFile(
    tmpPath: $file['tmp_name'],
    extension: $validation->extension,
    originalName: $validation->originalName
);
```

### Store a local file

```php
$stored = $Storage->storeLocalFile(
    sourcePath: '/temporary/report.pdf',
    extension: 'pdf',
    originalName: 'annual-report.pdf'
);
```

Both operations return:

```php
[
    'stored_name' => 'generated-name.pdf',
    'relative_path' => 'uploads/2026/09/generated-name.pdf',
    'absolute_path' => '/var/www/example.com/storage/uploads/2026/09/generated-name.pdf',
]
```

### Directory strategies

The current implementation supports:

```text
date  → uploads/YYYY/MM
flat  → uploads
```

Other values throw `RuntimeException`.

### Naming strategies

Supported generated-name strategies are:

```text
uuid
random
```

The recommended configuration keeps:

```php
'preserve_original_name' => false;
```

The original name remains database metadata while the stored filename avoids collisions and accidental disclosure.

When original-name preservation is enabled, AssetStorage sanitizes the filename and appends random bytes.

### Path methods

```text
absolutePath
storagePath
uploadRootPath
tempRootPath
exists
delete
```

`absolutePath()` resolves paths relative to the configured storage root and checks resolved directories against that root.

Only trusted database paths or application-generated relative paths should be supplied.

## Asset

`Asset` records metadata for a file already written to storage.

`register()` stores:

```text
UUID
owner type, ID and slot
asset-type ID
original and stored names
relative disk path
detected MIME type
extension
size
SHA-256 checksum
visibility
uploader
status and timestamps
```

It does not validate or move the file.

### Retrieval methods

| Method                                                                      | Purpose                                           |
|-----------------------------------------------------------------------------|---------------------------------------------------|
| `getByUuid(string $uuid)`                                                   | Find an asset regardless of visibility or status. |
| `getPublicByUuid(string $uuid)`                                             | Find an asset with public visibility.             |
| `latestForOwnerSlot(string $ownerType, string $ownerId, string $ownerSlot)` | Find the latest active asset in a slot.           |
| `deactivateForOwnerSlotExcept(...)`                                         | Deactivate older records in a slot.               |
| `deactivateByUuidForOwner(...)`                                             | Deactivate a matching owner asset.                |
| `getByOwner(string $ownerType, ...)`                                        | Get assets bases on owner signature.              |

Registry-based owner types currently use string slugs such as `user`. 

Deactivation changes database status. It does not delete the physical file.

## AssetUploader

AssetUploader coordinates:

1. policy resolution;
2. upload validation;
3. storage;
4. database registration;
5. optional deactivation of previous slot records.

If registration fails after storage, the newly stored file is removed.

If replacement is active, older matching records are marked inactive after the new record is registered. Existing physical files remain on disk.

Replacement is not currently wrapped in a database transaction, and the deactivation result is not returned separately. Applications requiring atomic replacement should extend this workflow.

### Multiple uploads

```php
$results = $Uploader->uploadMany(
    files: $normalizedFiles,
    ownerType: 'project',
    ownerId: (string) $projectId,
    ownerSlot: 'documents',
    uploadedBy: $userId
);
```

The result is an array of independent `AssetUploadResult` objects. One failure does not roll back earlier successful uploads.

### AssetUploadResult

Readonly properties are:

```php
$result->success;
$result->asset;
$result->storedFile;
$result->validation;
$result->error;
```

Helpers are:

```php
$result->passes();
$result->fails();
$result->insertId();
$result->errorMessage();
```

`errorMessage()` checks the direct exception, validation errors and database error in that order.

## Asset delivery

Assets are delivered through application routes:

```text
/asset/view/{uuid}
/asset/download/{uuid}
```

`AssetCtrl`:

1. loads the asset by UUID;
2. returns 404 when it is missing;
3. returns 404 when it is inactive;
4. requires authentication for every visibility except `public`;
5. delegates the response to `AssetDelivery`.

Inline delivery sends:

```http
Content-Disposition: inline
```

Download delivery sends:

```http
Content-Disposition: attachment
```

Both include the stored MIME type, content length and:

```http
X-Content-Type-Options: nosniff
```

`AssetDelivery` terminates execution after streaming the file.

### Current visibility boundary

The current controller treats visibility as:

```text
public
    no authentication required

private
restricted
    any authenticated session required
```

It does not currently distinguish ownership, role or a resource-specific ability for private and restricted assets.

Applications needing stronger rules must apply them in the controller before delivery.

The current delivery path also does not enforce these configuration entries:

```text
yarn.delivery.inline_mimes
yarn.delivery.force_download_by_default
```

They express intended policy but are not yet active runtime controls.

## Storage lifecycle

The database and filesystem are related but separate:

```text
registration failure
    newly stored file is removed

soft deactivation
    database status changes
    file remains stored

physical deletion
    must be requested through AssetStorage
```

An application should define retention and physical cleanup rules before accumulating replaceable or temporary assets.

## Security rules

```text
store files outside the public web root
detect MIME type on the server
check extension against detected MIME
generate stored filenames
bind database values
validate owner and slot vocabulary
check status before delivery
apply access policy in the controller
never expose absolute storage paths
```

Yarn manages file mechanics. The application remains responsible for deciding who may view a particular resource.
