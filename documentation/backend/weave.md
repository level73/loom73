# Weave

Weave is Loom73's application orchestration layer.

Controllers under:

```php
Loom73\Weave\Controllers
```

combine request handling, authorization, models, services, views and response selection.

Reusable mechanisms belong to Woodframe, Beam, Heddle, Yarn or Ledger. Decisions about what the application does belong to Weave.

## Current controllers

| Controller | Responsibility |
| --- | --- |
| `MainCtrl` | Public landing, architecture and requirements pages. |
| `ComponentsCtrl` | Public component demonstrations. |
| `UserCtrl` | Authentication, recovery, profiles and user administration. |
| `AssetCtrl` | Inline and attachment delivery for Yarn assets. |
| `ApiCtrl` | Explicit read-only JSON resources. |

These are the blueprint's reference application. A fork should add or replace controllers according to its domain.

## Creating a controller

```php
namespace Loom73\Weave\Controllers;

use Loom73\Beam\Article;
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

        $this->Article = new Article();
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

Concrete models currently live in:

```text
application/models/
```

They extend `Loom73\Beam\Model`.

Although the application models currently use the `Loom73\Beam` namespace, they remain application-specific data-access classes. A future reorganization may place them under `Loom73\Weave\Models`, matching the model name assembled by the router.

Controllers already prefer explicit model construction, so they do not depend on automatic model creation.

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