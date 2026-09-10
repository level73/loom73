# Woodframe

Woodframe is the structural foundation of Loom73.

It provides controller behavior, template composition, configuration, user feedback, operational logging, email delivery and shared registries.

## Class index

| Class | Responsibility |
| --- | --- |
| `Loom73\Woodframe\Config` | Load and retrieve component configuration. |
| `Loom73\Woodframe\Ctrl` | Base behavior for application controllers. |
| `Loom73\Woodframe\Template` | Resolve and compose application views. |
| `Loom73\Woodframe\Flash` | Store feedback for the next request. |
| `Loom73\Woodframe\Logger` | Record operational errors. |
| `Loom73\Woodframe\Mailman` | Send HTML email through PHPMailer and SMTP. |
| `Loom73\Woodframe\OwnerRegistry` | Describe application entities and their asset slots. |
| `Loom73\Woodframe\Debugger` | Render development backtraces. |
| `Loom73\Woodframe\Errata` | Legacy HTML-oriented exception helper. |

## Config

`Config` loads PHP configuration arrays and makes them available through dot notation.

Module files live in:

```text
config/modules/
```

Each file must return an array:

```php
<?php

return [
    'enabled' => true,
    'retention' => 30,
];
```

If the file is named `ledger.php`, its values become available under `ledger`:

```php
use Loom73\Woodframe\Config;

$enabled = Config::get('ledger.enabled', false);
$retention = Config::get('ledger.retention', 0);
```

The application bootstrap loads the module directory before dispatching the controller.

### Public methods

| Method | Result |
| --- | --- |
| `Config::load(string $path)` | Loads every PHP file in a directory. |
| `Config::loadFile(string $file)` | Loads one PHP configuration file. |
| `Config::get(string $key, mixed $default = null)` | Reads a value using dot notation. |
| `Config::set(string $key, mixed $value)` | Sets or overrides a value in memory. |
| `Config::has(string $key)` | Reports whether a key exists, including keys containing `null`. |
| `Config::all()` | Returns all loaded configuration. |
| `Config::clear()` | Removes all configuration held in memory. |

An empty key returns the complete configuration collection:

```php
$configuration = Config::get('');
```

Missing directories, missing files and module files that do not return arrays throw `RuntimeException`.

`set()` and `clear()` are useful for tests or an intentional re-bootstrap. Overrides last only for the current PHP process.

### Environment values

The global `env()` helper reads values loaded into `$_SERVER`.

It normalizes the strings:

```text
true
false
null
empty
```

Other values remain strings unless the module casts them:

```php
'max_upload_size' => (int) env(
    'YARN_MAX_UPLOAD_SIZE',
    10 * 1024 * 1024
),
```

Secrets belong in `config/.env`, not in module files.

## Ctrl

Application controllers extend `Loom73\Woodframe\Ctrl`.

The router supplies the model, controller and method names:

```php
class ArticleCtrl extends Ctrl
{
    public function index(): void
    {
        $this->set('title', 'Articles');
        $this->set(
            'meta_description',
            'Current articles.'
        );
    }
}
```

Normal actions prepare a response and allow the controller destructor to render it.

### Bootstrapped state

The constructor prepares:

```php
$this->_controller;
$this->_method;
$this->_model;
$this->_template;
$this->Auth;
$this->user;
$this->isAuthenticated;
```

It also supplies the template with:

```text
xss
bodyClass
css
js
is_authenticated
user, when authenticated
```

`_model` remains available for compatibility. New controllers should prefer explicitly named dependencies.

### Template variables

Pass data to the current view with:

```php
$this->set('title', 'Edit article');
$this->set('article', $article);
```

The view receives these values as ordinary PHP variables:

```php
<h1><?php echo htmlspecialchars($title); ?></h1>
```

`set()` does not escape values. The view must escape each value for its actual output context.

### Request methods

| Method | Purpose |
| --- | --- |
| `requestMethod()` | Returns the uppercase HTTP method. |
| `isPost(bool $notEmpty = false, bool $csrf = true)` | Checks POST, optional body presence and CSRF. |
| `isGet(bool $notEmpty = false)` | Checks GET and optional query-string presence. |
| `httpCheck(string $method, bool $notEmpty = false)` | Compatibility method; prefer `isPost()` and `isGet()`. |
| `post(string $name, mixed $default = null)` | Returns a trimmed, unfiltered POST value. |
| `posted(string $name, int|false $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS, mixed $default = false)` | Returns a trimmed and optionally filtered POST value. |
| `validateToken()` | Compares the submitted CSRF token with the session token. |
| `destroyToken()` | Removes the current CSRF token. |

Example:

```php
public function save(): void
{
    if (!$this->isPost(notEmpty: true)):
        return;
    endif;

    $title = $this->posted('title');
    $email = $this->posted(
        'email',
        FILTER_VALIDATE_EMAIL,
        null
    );

    // Validate and perform the action.
}
```

Use `filter: false` for values that must not be HTML-encoded during input handling, such as passwords:

```php
$password = $this->posted(
    'password',
    false,
    null
);
```

Input filtering does not replace validation. HTML input filtering also does not replace output escaping.

### CSRF

The global `csrf()` helper prints a hidden field:

```php
<form method="post" action="/article/save">
    <?php csrf(); ?>
</form>
```

The generated field is:

```html
<input type="hidden" name="csrf" value="...">
```

`isPost()` validates it by default.

The token belongs to:

```php
$_SESSION[$_SERVER['APPNAME']]['xss']
```

Destroying the token causes the next form render to generate a new one.

### Access guards

Controllers apply access requirements explicitly:

```php
$this->requireAuth();
$this->requireAdmin();
$this->requireEditor();
$this->requireAbility('manage_users');
```

The guards currently redirect:

```text
unauthenticated  → /user/login
unauthorized     → /main/forbidden
```

Calling `Auth` in the base controller establishes authentication context. It does not automatically protect an action.

### Upload helpers

Controllers can normalize single and multiple `$_FILES` entries:

```php
$this->hasUploadedFile('documents');
$this->uploadedFile('avatar');
$this->uploadedFiles('documents');
```

They can pass those files to Yarn with:

```php
$result = $this->uploadAsset(
    field: 'avatar',
    ownerType: 'user',
    ownerId: (string) $userId,
    ownerSlot: 'avatar',
    uploadedBy: $userId
);
```

Multiple files use:

```php
$results = $this->uploadAssets(
    field: 'documents',
    ownerType: 'project',
    ownerId: (string) $projectId,
    ownerSlot: 'documents',
    uploadedBy: $userId
);
```

The complete upload contract is documented in [Yarn](yarn.md).

### Non-HTML responses

Use:

```php
$this->disableRender();
```

before producing JSON, a download or another response that must bypass normal template composition.

Redirect helpers call it automatically.

`enableRender()` restores automatic rendering when deliberately needed.

### Redirects and QueryResult handling

Available redirect helpers are:

```php
$this->redirect($url);
$this->redirectWithSuccess($url, $message);
$this->redirectWithWarning($url, $message);
$this->redirectWithError($url, $message);
$this->redirectWithInfo($url, $message);
```

They set a `Location` header and terminate execution.

`evaluateResponse()` provides the common mutation flow:

```php
$result = $this->Article->updateById(
    $data,
    $articleId
);

$this->evaluateResponse(
    result: $result,
    id: $articleId,
    successMessage: 'Article updated.',
    errorMessage: 'The article could not be updated.'
);
```

On failure it redirects back with an error. On success it builds a resource URL from the controller provider, fragment and record ID.

Duplicate database entries receive the shared duplicate-entry message.

Diagnostic exception details are included only when `DEBUG` is enabled.

### Ledger helpers

Record an authenticated action with:

```php
$this->recordAction(
    action: 'article.update',
    ownerType: 'article',
    ownerId: (string) $articleId,
    summary: 'Article updated',
    metadata: [
        'updated_fields' => array_keys($data),
    ]
);
```

For a flow without an authenticated actor:

```php
$this->recordAnonymousAction(
    action: 'auth.recover',
    ownerType: 'user',
    ownerId: (string) $userId,
    summary: 'Password recovery requested'
);
```

Authenticated actions require a valid actor ID. A missing actor is logged and returns `false`.

See [Ledger](ledger.md) for event rules.

### Automatic rendering

`Ctrl::__destruct()` renders when `shouldRender` remains true.

A normal action therefore finishes after preparing view data:

```php
public function index(): void
{
    $this->set('title', 'Articles');
    $this->set('articles', $this->Article->all());
}
```

Redirect, JSON and binary responses must disable normal rendering.

## Template

`Template` maps a controller and method to:

```text
application/views/{controller}/{method}.php
```

Example:

```text
ArticleCtrl::edit()
    → application/views/article/edit.php
```

Variables assigned with `Ctrl::set()` or `Template::set()` are extracted before inclusion.

### Standard composition

A normal HTML page is composed from:

```text
application/views/head.php
application/views/{controller}/header.php or application/views/header.php
application/views/{controller}/sub-menu.php, when present
application/views/{controller}/{method}.php
application/views/{controller}/footer.php or application/views/footer.php
application/views/foot.php
```

A missing view produces HTTP 500 and renders:

```text
application/views/errors/500.php
```

The missing internal path may be made available to the error view, but production output must not expose it.

### Routes excluded from page composition

The current template skips standard head, header and footer composition for:

```text
controller: ajax
controller: api
method: export
method: download
method: access
method: save
```

This convention is based on route names. Controllers returning direct responses should still call `disableRender()` explicitly so their intent remains visible.

## Flash

Flash stores feedback in the named application session and makes it available after a redirect.

```php
Flash::success([
    'message' => 'Profile updated.',
    'data' => null,
    'error' => null,
]);
```

Available types are:

```php
Flash::success($message);
Flash::error($message);
Flash::warning($message);
Flash::info($message);
Flash::add($type, $message);
```

`Flash::all()` returns every pending message and removes them from the session. Reading is therefore destructive and should normally happen once in the shared message partial.

The conventional message shape is:

```php
[
    'message' => 'Text intended for the user.',
    'data' => null,
    'error' => null,
]
```

See [Labels and user feedback](../labels.md) for message conventions.

## Logger

Logger records operational failures in:

```text
storage/logs/loom73.log
```

Example:

```php
Logger::error(
    'AssetUploader',
    'Unable to register stored asset',
    [
        'owner_type' => $ownerType,
        'owner_id' => $ownerId,
    ]
);
```

Each entry includes:

```text
timestamp
ERROR level
source
message
calling file and line
optional JSON context
```

If the file cannot be written, Logger falls back to PHP's `error_log()`.

Logging must not contain passwords, session tokens, recovery codes or other secrets. Avoid storing full request bodies or uploaded-file contents in context.

Logger records operational failures. User actions belong to [Ledger](ledger.md).

## Mailman

Mailman sends HTML email through PHPMailer:

```php
$result = Mailman::sendMail(
    [
        'email' => 'support@example.com',
        'name' => 'Example Support',
    ],
    'person@example.com',
    'Password reset',
    $htmlBody
);
```

The current method uses application configuration as the actual sender:

```text
APPEMAIL
APPNAME
MAIL_SMTP_HOST
MAIL_SMTP_USERNAME
MAIL_SMTP_PASSWORD
MAIL_SMTP_PORT
```

The `from` argument becomes the `Reply-To` address.

The adapter currently uses SMTP authentication and `PHPMailer::ENCRYPTION_SMTPS`. The configured host and port must support that mode.

The result is:

```text
true
    the message was sent

PHPMailer\Exception
    sending failed
```

Failures are also sent to Logger.

Mailman treats the body as HTML. Construct application messages from trusted templates and escape inserted values for their HTML context.

## OwnerRegistry

OwnerRegistry describes application entities and the asset slots they support.

It reads:

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

Public methods include:

```text
all
has
get
label
model
primaryKey
assetSlots
hasAssetSlot
assetSlot
slotAllowsMultiple
slotReplacesExisting
defaultAssetType
defaultVisibility
slotAllowsAssetType
```

Unknown owners and unknown slots throw `InvalidArgumentException`.

An empty `allowed_asset_types` collection permits every registered asset type. Explicit lists are safer for constrained slots.

OwnerRegistry is shared by Yarn policy and Ledger event validation.

## Development helpers

### Debugger

`Debugger::dbg()` renders backtrace data as HTML. It may expose filesystem paths, arguments and internal state.

Use it only during local development.

### Errata

`Errata` is a legacy exception helper that returns formatted HTML from `errorMessage()`.

Current routing errors use the dedicated 404 and 500 templates. New backend code should normally return structured results, throw standard exceptions or log operational failures according to its boundary.

### Global view helpers

Loom73 currently provides these global helpers:

| Helper | Purpose |
| --- | --- |
| `partial($name)` | Includes `application/views/_partials/{name}.php`. |
| `component($name, $label, $options)` | Includes `application/views/_components/{name}.php`. |
| `csrf()` | Prints the CSRF field or token. |
| `idfield($id)` | Prints a hidden `id` field. |
| `flashIcon($type, $size)` | Prints a corresponding Stitch icon. |
| `prettyPrint($value)` | Displays development data. |
| `prettySQL($sql)` | Displays SQL and its origin. |
| `prettyString($string)` | Displays a string and its origin. |
| `toMachine($value, $type)` | Converts route fragments into class or method names. |

Partial and component names must be developer-controlled literals. Do not derive include names from request values.

The pretty-print helpers expose internal paths and data and belong only in development output.