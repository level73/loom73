# Loom73 MVC

Loom73 follows a small, explicit MVC architecture.

It is not meant to be a full-stack framework, and it does not try to hide the request flow behind excessive machinery.

The goal is simple:

```text id="62pte0"
URL → Controller → Method → Model/Service when needed → View/Redirect/Response
```

Loom73 MVC is based on convention, not magic.

---

## Routing

Routing is intentionally straightforward.

A request path is interpreted as follows:

```text id="oymaxf"
first URL fragment   → controller
second URL fragment  → method
remaining fragments  → method parameters
```

Example:

```text id="jb6yft"
example.com/the-fancy-pants/more-pants/12/edit
```

routes to:

```php id="lu6953"
TheFancyPantsCtrl::more_pants(12, 'edit')
```

Controller and method names do not need to respect PHP casing or underscore conventions in the URL.

The router normalizes incoming route fragments through the `toMachine()` function.

The route:

```text id="ha6de6"
the-fancy-pants
```

becomes:

```php id="5plqwb"
TheFancyPantsCtrl
```

The route:

```text id="pc17wp"
more-pants
```

becomes:

```php id="nl0s36"
more_pants()
```

This allows URLs to remain readable while PHP code keeps its normal class and method naming conventions.

---

## Default route

The default controller is:

```php id="4yb5d0"
MainCtrl
```

The default method is:

```php id="7a7aap"
index()
```

So the base URL routes to:

```php id="g3ygxq"
MainCtrl::index()
```

---

## Controllers

Controllers live in the application layer.

A typical controller is located in:

```text id="j05eob"
/application/controllers/
```

Controllers may use the namespace:

```php id="bejtaz"
Loom73\Weave\Controllers
```

because controllers belong to the orchestration layer of the application.

A controller is responsible for coordinating the request:

```text id="98cj4c"
read request data
apply guards when needed
call models or services
set view data
redirect after mutations
return binary/custom responses when necessary
```

The base controller is:

```php id="k7d8ap"
Loom73\Woodframe\Ctrl
```

It provides shared controller behavior such as:

```text id="w7ad82"
view data assignment
template coordination
redirect helpers
authentication context bootstrapping
guard helpers
render control
```

A controller should remain readable. It should express the application flow clearly without becoming a dumping ground for domain logic.

---

## Controller/model relationship

Controllers do **not** need to have a model by default.

Earlier Loom73 prototypes allowed automatic model instantiation, but the current direction is more explicit:

```text id="l6vfga"
Controllers instantiate the models or services they actually use.
```

This avoids false coupling and keeps each controller honest about its dependencies.

Good:

```php id="rsext6"
use Loom73\Beam\User;
use Loom73\Yarn\Asset;

class UserCtrl extends Ctrl
{
    protected User $User;

    public function __construct(
        string $model,
        string $controller,
        string $method
    ) {
        parent::__construct($model, $controller, $method);

        $this->User = new User();
    }
}
```

Avoid assuming that every controller must have:

```php id="zghwl6"
$this->_model
```

Some controllers coordinate views, static pages, auth flows, asset delivery or CLI-adjacent operations and may not need a domain model at all.

The principle is:

```text id="lqm8ky"
Instantiate what you use.
Do not create dependencies just because the pattern expects them.
```

---

## Controller property naming

Loom73 follows a practical naming convention inside controllers.

Dependency/model/service properties use PascalCase:

```php id="r272ks"
$this->User
$this->Asset
$this->Auth
$this->Session
```

Runtime data or records use lowercase:

```php id="ht3j1q"
$this->user
$profile
$result
$avatar
```

This makes controller code easier to scan.

Example:

```php id="f2xai2"
$profile = $this->user?->first();

$avatar = $this->Asset->latestForOwnerSlot(
    ownerType: 'user',
    ownerId: (string) $profile->id,
    ownerSlot: 'avatar'
);
```

---

## Request method convention

Loom73 uses a simple request convention:

```text id="rhzn1f"
GET  → show pages, forms, lists and details
POST → perform actions and mutations
```

A GET method may read from the database and prepare view data.

A POST method should usually:

```text id="vkg3sp"
validate input
perform the action
set feedback/flash message
redirect
```

This avoids duplicate GET/POST controller methods and keeps mutation flows predictable.

Examples:

```text id="dc8gvq"
/user/create          GET  → show creation form
/user/edit/{id}       GET  → show edit form
/user/save            POST → create or update user

/user/profile         GET  → show profile
/user/profile-update  POST → update profile and avatar

/user/login           GET  → show login form
/user/access          POST → authenticate

/user/recover         GET  → show recovery form
/user/recover-send    POST → send recovery email

/user/reset/{hash}    GET  → show reset form
/user/reset-save      POST → update password
```

The principle:

```text id="53gevz"
GET prepares.
POST changes.
POST redirects.
```

---

## Authentication context and guards

`Ctrl` may bootstrap the authentication context so that controllers can access the current user when available.

This does **not** mean every route is private.

Public routes remain public unless a controller method explicitly calls a guard.

Typical guards:

```php id="45yhgg"
$this->requireAuth();
$this->requireAbility('manage_users');
$this->requireAdmin();
```

Example:

```php id="gqayrc"
public function profile(): void
{
    $this->requireAuth();

    // profile logic
}
```

A login page, recovery page or public landing page simply does not call `requireAuth()`.

The rule is:

```text id="2qg117"
Auth context may be global.
Access control must remain explicit.
```

---

## Passing data to views

The base controller provides a simple mechanism for assigning data to the view.

Typical usage:

```php id="hj1o8k"
$this->set('title', 'Edit profile');
$this->set('user', $this->user);
$this->set('avatar', $avatar);
```

The view then receives these variables through the template layer.

The base controller coordinates this process; individual controllers should not need to manually assemble templates in ordinary cases.

---

## Views

Views live in:

```text id="3gehqb"
/application/views/
```

Each controller should have a matching view directory named after the controller without the `Ctrl` suffix, in lowercase.

Example:

```text id="aat2e3"
MainCtrl
```

uses:

```text id="cdqz6r"
/application/views/main/
```

A method normally maps to a view file with the same name.

Example:

```php id="cgn70p"
MainCtrl::index()
```

uses:

```text id="s5g682"
/application/views/main/index.php
```

Another example:

```php id="w1ecqu"
UserCtrl::profile()
```

uses:

```text id="5w21mk"
/application/views/user/profile.php
```

Method names normalized from dashed URLs retain their PHP underscore form.

So:

```text id="gxya8y"
/user/profile-update
```

routes to:

```php id="u4opvu"
UserCtrl::profile_update()
```

and may use:

```text id="vvbxq7"
/application/views/user/profile_update.php
```

when a view is needed.

---

## Template assembly

The template layer is handled by:

```php id="4wxqnj"
Loom73\Woodframe\Template
```

The template class assembles the final page output from the selected view and the surrounding layout/template parts.

In ordinary application work, this class should rarely need to be touched.

Controllers prepare data.

Views render local markup.

The template assembles the response.

---

## Disabling automatic rendering

Some routes do not return an HTML view.

Examples:

```text id="ylqfbj"
asset delivery
file download
JSON response
manual redirect
binary output
```

In these cases, automatic template rendering must be disabled.

Example:

```php id="8g36ne"
public function view(string $uuid): void
{
    $this->disableRender();

    // asset delivery logic
}
```

This is especially important for binary responses. If the template appends HTML after an image, the browser may receive a corrupted image even if the response has the correct `Content-Type`.

The rule is:

```text id="az30uf"
If the controller sends the full response itself, disable rendering.
```

---

## Models

Models that need database access should extend:

```php id="7scu6m"
Loom73\Beam\Model
```

A model defines its persistence target through protected properties:

```php id="qa5dbv"
protected string $table;
protected string $pkey;
protected bool $dates;
```

Example:

```php id="7xzq3x"
use Loom73\Beam\Model;

class User extends Model
{
    protected string $table = 'auth_user';
    protected string $pkey = 'idauth_user';
    protected bool $dates = true;
}
```

These properties describe:

```text id="an52vl"
$table  → database table name
$pkey   → primary key column
$dates  → whether the table has created_at / modified_at fields
```

A model may then use the base CRUD helpers provided by `Beam\Model`.

However, models are not limited to generic CRUD.

Concrete models should write explicit SQL when that is clearer.

Use explicit SQL for:

```text id="19yxo1"
joins
reports
aggregations
complex filters
domain-specific queries
performance-sensitive reads
```

The principle is:

```text id="fc9qmd"
Beam reduces repetition.
It does not hide SQL.
```

---

## Query results

Database operations return:

```php id="60vhqr"
Loom73\Beam\QueryResult
```

`QueryResult` makes the outcome of a query explicit.

It can represent:

```text id="ff7u3v"
success with rows
success without rows
failed query
insert result
database error
```

Typical usage:

```php id="qywfhn"
$result = $this->User->getById($id);

if ($result->fails() || $result->isEmpty()) {
    $this->redirect('/user/list');
}

$user = $result->first();
```

This avoids spreading raw PDO handling through controllers.

---

## Application flow example

A typical GET method:

```php id="k9wcix"
public function profile(): void
{
    $this->requireAuth();

    $this->set('title', 'Edit profile');
    $this->set('user', $this->user);

    $profile = $this->user?->first();

    if (!$profile) {
        return;
    }

    $avatar = $this->Asset->latestForOwnerSlot(
        ownerType: 'user',
        ownerId: (string) $profile->id,
        ownerSlot: 'avatar'
    );

    $this->set('avatar', $avatar);
}
```

A typical POST method:

```php id="bkpwzc"
public function profile_update(): void
{
    $this->requireAuth();

    // validate input
    // update profile
    // handle avatar upload if present
    // set feedback

    $this->redirect('/user/profile');
}
```

A binary route:

```php id="5rkwvc"
public function view(string $uuid): void
{
    $this->disableRender();

    // validate asset
    // check visibility
    // deliver file
}
```

---

## MVC boundaries

### Controller

A controller should coordinate the request.

It may:

```text id="8f5obp"
read request data
call models/services
apply guards
set view variables
redirect
disable rendering for custom responses
```

It should not contain heavy persistence logic or reusable infrastructure code.

---

### Model

A model should handle persistence for a domain object or database-backed concept.

It may:

```text id="u3x3ox"
use Beam CRUD helpers
write explicit SQL
return QueryResult
encapsulate domain-specific data access
```

It should not know about HTML templates or request routing.

---

### View

A view should render markup.

It may:

```text id="z4bymn"
read assigned variables
escape output
display forms
display records
include small presentational logic
```

It should not perform database operations or business decisions.

---

### Template

The template assembles the final response.

It should remain generic and rarely need project-specific changes.

---

## Summary

```text id="mch3ec"
URL fragment 1
  selects the controller

URL fragment 2
  selects the method

Remaining fragments
  become method parameters

Controllers
  orchestrate requests

Models
  handle persistence when needed

Views
  render application markup

Template
  assembles the final response

GET
  prepares and displays

POST
  changes state and redirects
```

The Loom73 MVC rule:

```text id="wd4hw7"
Keep routing simple.
Keep controllers explicit.
Keep models honest.
Keep SQL visible when it matters.
Keep views focused on rendering.
```
