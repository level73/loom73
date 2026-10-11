# Weave

Weave is Loom73's HTTP application layer. Its controllers coordinate request handling, authorization, models and services, view data and response selection.

Application controllers live in `application/controllers/` and use the `Loom73\Weave\Controllers` namespace. Loom73's upgradeable controllers live in `lib/Weave/Controllers/Core/` and use `Loom73\Weave\Controllers\Core`.

Reusable mechanisms belong to Woodframe, Beam, Heddle, Yarn or Ledger. Weave decides how an HTTP request uses them.

## Current controllers

| Layer | Controller | Responsibility |
| --- | --- | --- |
| Application | `MainCtrl` | Public landing, architecture and requirements pages. |
| Application | `ComponentsCtrl` | Public component demonstrations. |
| Core | `ApiCtrl` | Read-only JSON resources. |
| Core | `AssetCtrl` | Inline and attachment delivery for Yarn assets. |
| Core | `GaugeCtrl` | Gauge opt-in and opt-out requests. |
| Core | `UserCtrl` | Authentication, recovery, profiles and user administration. |

The controller map in `config/modules/routes.php` provides the core defaults. An explicit application override has priority, followed by a matching controller in `application/controllers/`, then the core default. The URL still follows `/controller/method/arguments`. All HTML views remain under `application/views/`. See [MVC routing](../mvc.md#routing) for the dispatch rules.

An application can extend `ApiCtrl`, `AssetCtrl` or `UserCtrl` to customize selected actions while inheriting the others. `GaugeCtrl` is final; an application that needs a different Gauge request flow must provide its own controller.

## Creating a controller

```php
namespace Loom73\Weave\Controllers;

use Loom73\Weave\Models\Article;
use Loom73\Woodframe\Ctrl;

class ArticleCtrl extends Ctrl
{
    protected Article $Article;

    public function __construct(
        string $model,
        string $controller,
        string $method
    ) {
        parent::__construct(
            $model,
            $controller,
            $method
        );

        $this->Article = $this->_model instanceof Article
            ? $this->_model
            : new Article();
    }

    public function index(): void
    {
        $this->set('title', 'Articles');
        $this->set(
            'meta_description',
            'Published articles.'
        );

        $this->set(
            'articles',
            $this->Article->all(
                orderBy: 'created_at',
                direction: 'DESC'
            )
        );
    }
}
```

The matching view is:

```text
application/views/article/index.php
```

## Controller actions

An action may produce:

```text
HTML view
redirect
JSON
inline file
download
other direct response
```

Ordinary HTML actions set template variables and return.

Mutations normally use POST, validate CSRF, perform the operation, add feedback and redirect.

```php
public function save(): void
{
    $this->requireEditor();

    if (!$this->isPost(notEmpty: true)):
        return;
    endif;

    $result = $this->Article->create([
        'title' => $this->posted('title'),
    ]);

    $this->evaluateResponse(
        result: $result,
        fragment: 'edit'
    );
}
```

JSON and binary actions call:

```php
$this->disableRender();
```

## Responsibility boundary

Controllers should contain:

```text
request-flow decisions
access guards
input validation and normalization
service coordination
response selection
flash-message selection
audit calls
```

Controllers should not contain:

```text
repeated SQL
generic storage mechanics
HTML rendering logic
reusable validation engines
configuration parsing
```

Domain SQL belongs in a concrete Beam model. Generic reusable behavior belongs in the namespace that owns it.

## Public application patterns

### Page action

```php
public function profile(): void
{
    $this->requireAuth();

    $this->set('title', 'Profile');
    $this->set('profile', $this->user);
}
```

### Mutation and redirect

```php
public function update(): void
{
    $this->requireAuth();

    if (!$this->isPost(notEmpty: true)):
        return;
    endif;

    $result = $this->Profile->updateById(
        $data,
        $profileId
    );

    $this->evaluateResponse(
        result: $result,
        id: $profileId
    );
}
```

### Binary response

```php
public function download(string $uuid): void
{
    $this->disableRender();

    // Retrieve, authorize and deliver the asset.
}
```

### Audit action

```php
$this->recordAction(
    action: 'article.publish',
    ownerType: 'article',
    ownerId: (string) $articleId,
    summary: 'Article published'
);
```

## Application models

New application models belong in:

```text
application/models/
```

They extend `Loom73\Beam\Model` and use `Loom73\Weave\Models`.

The `Ctrl` class makes the corresponding model available in `$_model` for compatibility, while core models like `Loom73\Beam\User` live in `lib/Beam`.

Controllers can reuse the matching model initialized by `Ctrl` or construct other named dependencies explicitly.

## Response safety

Controllers must:

```text
guard protected actions
validate HTTP methods
validate CSRF for mutations
validate domain input
avoid exposing exception detail in production
escape output in views
select explicit fields for JSON
authorize assets before delivery
```

A hidden link or absent navigation item does not protect a controller method.

See [MVC](../mvc.md) for routing and template composition.