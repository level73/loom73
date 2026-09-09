
# Loom73 MVC

Loom73 uses a small, explicit MVC architecture.

It does not hide the request flow behind a route registry, service container or automatic controller wiring.

```text
URL
  → Controller
  → Method
  → Model or service when needed
  → View, redirect, JSON or binary response
```

## Application bootstrap

All public HTTP requests enter through:

```text
public_html/public/index.php
```

The bootstrap:

1. defines application paths;
2. loads Composer dependencies and Loom73 configuration;
3. loads `config/.env`;
4. starts the named PHP session;
5. configures the timezone and HTML content type;
6. registers the Loom73 autoloader;
7. loads module configuration;
8. dispatches the request.

The web server document root must point to:

```text
public_html/public
```

Application code, configuration, storage and vendor files must remain outside the public web root.

## Routing

The rewritten request path is read from the `url` query parameter and split into fragments.

```text
first fragment       controller
second fragment      method
remaining fragments  method arguments
```

For example:

```text
/user/profile
```

dispatches to:

```php
Loom73\Weave\Controllers\UserCtrl::profile()
```

and:

```text
/asset/view/550e8400-e29b-41d4-a716-446655440000
```

dispatches to:

```php
Loom73\Weave\Controllers\AssetCtrl::view(
    '550e8400-e29b-41d4-a716-446655440000'
);
```

Hyphenated controller fragments become PascalCase class names:

```text
technical-requirements
    → TechnicalRequirementsCtrl
```

Hyphenated method fragments become underscore-separated method names:

```text
profile-update
    → profile_update()
```

The default controller and method come from:

```env
DEFAULT_CONTROLLER='main'
DEFAULT_METHOD='index'
```

The base URL therefore normally resolves to:

```php
MainCtrl::index()
```

## Missing routes and views

If the controller or method does not exist, Loom73 returns:

```text
HTTP 404
```

and renders the public error view:

```text
application/views/errors/404.php
```

If a dispatched controller method has no corresponding view, `Template` returns:

```text
HTTP 500
```

and renders:

```text
application/views/errors/500.php
```

Internal path information is shown only when:

```env
SYSTEM_STATUS='development'
```

Production error pages remain generic.

## Controllers

Application controllers live in:

```text
application/controllers/
```

and use:

```php
Loom73\Weave\Controllers
```

Controllers extend:

```php
Loom73\Woodframe\Ctrl
```

A controller coordinates the request. It may:

```text
inspect request data
apply authentication or ability guards
call models and services
set template variables
record meaningful actions
redirect after a mutation
disable normal rendering for JSON or binary responses
```

A controller should make the application flow easy to follow.

## Dependencies

Controllers should instantiate the models and services they actually use.

```php
use Loom73\Beam\User;
use Loom73\Woodframe\Ctrl;
use Loom73\Yarn\Asset;

class UserCtrl extends Ctrl
{
    protected User $User;
    protected Asset $Asset;

    public function __construct(
        string $model,
        string $controller,
        string $method
    ) {
        parent::__construct($model, $controller, $method);

        $this->User = new User();
        $this->Asset = new Asset();
    }
}
```

`Ctrl` retains an optional `_model` property for compatibility and generic helpers. New controller code should prefer explicitly named dependencies.

```text
Instantiate what you use.
Keep dependencies visible.
```

## Controller property names

Models, services and reusable components use PascalCase properties:

```php
$this->User
$this->Asset
$this->Session
$this->Auth
```

Records, query results and other runtime data use camelCase:

```php
$user
$asset
$profile
$result
```

This is a Loom73 convention rather than a general PHP requirement.

## GET and POST

Loom73 follows a predictable request convention:

```text
GET   prepares and displays
POST  changes state and redirects
```

A GET action may retrieve data and render a page or form.

A POST action should normally:

1. verify the request method and CSRF token;
2. read and validate input;
3. perform the mutation;
4. add a flash message;
5. redirect.

Example:

```php
public function save(): void
{
    if (!$this->isPost(notEmpty: true)):
        return;
    endif;

    $result = $this->User->create([
        // Validated values.
    ]);

    $this->evaluateResponse($result);
}
```

`isPost()` validates CSRF by default:

```php
$this->isPost();
$this->isPost(notEmpty: true);
$this->isPost(notEmpty: true, csrf: false);
```

`isGet()` checks GET requests:

```php
$this->isGet();
$this->isGet(notEmpty: true);
```

`httpCheck()` remains available for compatibility. New code should prefer `isPost()` and `isGet()`.

## Reading POST values

`post()` returns a trimmed value without applying a filter:

```php
$password = $this->post('password');
```

`posted()` preserves Loom73's historical default filtering:

```php
$username = $this->posted('username');
$email = $this->posted(
    'email',
    FILTER_SANITIZE_EMAIL
);
$password = $this->posted(
    'password',
    false
);
```

Input normalization and output escaping solve different problems.

```text
Input:
    normalize and validate according to the field

Output:
    escape according to the destination context
```

Views must still escape values for HTML, attributes, URLs, JSON or other output contexts as appropriate.

## CSRF

Loom73 stores the CSRF token in the application session.

Views can print a hidden field with:

```php
<?php csrf(); ?>
```

POST actions normally validate it through `isPost()`.

After a completed mutation or authentication attempt, the controller may destroy the current token so a new one is generated for the next form.

## Authentication and guards

The base controller initializes the authentication context.

Available state includes:

```php
$this->Auth
$this->isAuthenticated
$this->user
```

This does not protect every route automatically.

Controllers apply guards explicitly:

```php
$this->requireAuth();
$this->requireAdmin();
$this->requireEditor();
$this->requireAbility('manage_users');
```

This keeps route access visible in the action that requires it.

## Models

Application data-access classes live in:

```text
application/models/
```

They extend:

```php
Loom73\Beam\Model
```

A model defines at least its table and normally its primary key:

```php
namespace Loom73\Beam;

class User extends Model
{
    protected string $table = 'auth_user';
    protected string $pkey = 'idauth_user';
    protected bool $dates = true;
    protected bool $softDeletes = true;
}
```

Use the common Model methods for ordinary persistence. Write explicit SQL in the concrete model when it expresses the domain more clearly.

```php
public function apiByUsername(string $username): QueryResult
{
    $sql = '
        SELECT username, role
        FROM auth_user
        WHERE username = :username
        LIMIT 1
    ';

    return $this->query($sql, [
        'username' => $username,
    ]);
}
```

SQL belongs in models or dedicated data-access services, not in views.

## QueryResult handling

Model operations return `QueryResult`.

```php
$result = $this->User->getById($id);

if ($result->fails()):
    // The database operation failed.
endif;

if ($result->isEmpty()):
    // The operation succeeded but returned no record.
endif;

$user = $result->first();
```

`evaluateResponse()` provides a common mutation flow for ordinary CRUD actions. Controllers may handle a result directly when the use case requires different behavior.

## Views and templates

Views live in:

```text
application/views/{controller}/{method}.php
```

For example:

```text
/user/profile
    → application/views/user/profile.php
```

Controllers assign variables with:

```php
$this->set('title', 'Profile');
$this->set('profile', $profile);
```

The template extracts those variables before composing the response.

A standard HTML response may include:

```text
application/views/head.php
controller-specific or global header
controller-specific submenu
the requested view
controller-specific or global footer
application/views/foot.php
```

A view may:

```text
render semantic markup
read assigned variables
escape output
include partials and presentational components
perform small display-only decisions
```

A view should not:

```text
query the database
make authorization decisions
mutate application state
perform redirects
```

## Automatic rendering

Normal controller actions do not call `render()` directly.

`Ctrl` renders the configured template from its destructor when:

```php
$this->shouldRender === true
```

This keeps ordinary page actions concise.

For responses that must bypass the HTML template, call:

```php
$this->disableRender();
```

This is used for responses such as:

```text
JSON
uploaded assets
downloads
exports
redirects
```

Redirect helpers disable rendering automatically and terminate execution.

## Flash messages and redirects

Mutation actions can redirect with a typed message:

```php
$this->redirectWithSuccess('/user/profile', [
    'message' => MSG_PROFILE_UPDATE_SUCCESS,
    'data' => null,
    'error' => null,
]);
```

Available helpers include:

```php
redirectWithSuccess()
redirectWithWarning()
redirectWithError()
redirectWithInfo()
```

Flash messages survive the redirect and are consumed when rendered.

Diagnostic error details should only be exposed while development debugging is enabled.

## Ledger

Controllers can record meaningful actions through:

```php
$this->recordAction(
    action: 'user.update',
    ownerType: 'user',
    ownerId: (string) $userId,
    summary: 'User profile updated'
);
```

Anonymous actions use:

```php
$this->recordAnonymousAction(...);
```

Ledger failures are logged but should not reverse a successful application operation.

## Alternative control syntax

Loom73 templates prefer PHP's alternative control syntax where it improves the readability of mixed PHP and HTML:

```php
<?php if ($is_authenticated): ?>
    <a href="/user/profile">Profile</a>
<?php else: ?>
    <a href="/user/login">Login</a>
<?php endif; ?>
```

Ordinary braces remain appropriate inside classes and for PHP-only logic.

## Request summary

```text
Request
  ↓
public/index.php bootstrap
  ↓
route fragments
  ↓
controller and method
  ↓
guards and input handling
  ↓
model or service
  ↓
QueryResult or domain result
  ↓
view, redirect, JSON or binary response
```

The MVC rule is:

```text
Keep routing simple.
Keep controllers explicit.
Keep models honest.
Keep SQL visible when it matters.
Keep views focused on rendering.
```