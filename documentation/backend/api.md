# API

The current Loom73 API is a small, explicit, read-only JSON layer.

It demonstrates how an application can expose selected resources without serializing complete models or database tables automatically.

## Current endpoints

```text
GET /api/users
GET /api/users/{username}
```

Both current user endpoints require an authenticated Loom73 session.

### User collection

```http
GET /api/users
```

Successful response:

```json
{
  "data": [
    {
      "username": "alan",
      "role": "admin"
    }
  ]
}
```

### One user

```http
GET /api/users/alan
```

Successful response:

```json
{
  "data": {
    "username": "alan",
    "role": "admin",
    "last_access": "2026-09-10 12:00:00"
  }
}
```

The concrete model selects the exposed fields. Password hashes, salts, email addresses and recovery values are not included.

## Error format

API errors use:

```json
{
  "error": {
    "code": "not_found",
    "message": "User not found."
  }
}
```

Current codes include:

```text
not_found
query_failed
method_not_allowed
```

Corresponding HTTP status codes include:

```text
404
500
405
```

A method error also returns:

```http
Allow: GET
```

## Read-only guard

Each endpoint must apply the GET guard explicitly:

```php
if (!$this->requireGet()):
    return;
endif;
```

Other methods receive:

```http
HTTP/1.1 405 Method Not Allowed
Allow: GET
Content-Type: application/json; charset=utf-8
```

The read-only rule is a controller convention enforced by `ApiCtrl::requireGet()`. Adding a new endpoint requires adding the guard to that endpoint.

## Creating an endpoint

Write an explicit model query:

```php
public function apiList(): QueryResult
{
    $sql = '
        SELECT
            article.slug,
            article.title
        FROM article
        WHERE article.status = :status
        ORDER BY article.title ASC
    ';

    return $this->query($sql, [
        'status' => 2,
    ]);
}
```

Then expose it from the API controller:

```php
public function articles(?string $slug = null): void
{
    $this->requireAuth();

    if (!$this->requireGet()):
        return;
    endif;

    $Articles = new Article();

    if ($slug === null):
        $result = $Articles->apiList();

        if ($result->fails()):
            $this->jsonError(
                code: 'query_failed',
                message: 'Unable to retrieve articles.',
                status: 500
            );

            return;
        endif;

        $this->json([
            'data' => $result->all(),
        ]);

        return;
    endif;

    // Retrieve and return one article.
}
```

Expose only the fields required by the API contract.

## Public and protected endpoints

The current user endpoints call:

```php
$this->requireAuth();
```

Without an authenticated session, the base guard redirects to `/user/login`. The unauthenticated response is therefore currently an HTML redirect rather than a JSON `401` error.

A public endpoint can omit that guard deliberately.

Session protection, public access and ability requirements must be visible in the endpoint method.

## JSON responses

`ApiCtrl::json()`:

1. disables template rendering;
2. sets the HTTP status;
3. sends `application/json; charset=utf-8`;
4. encodes without escaping Unicode or slashes.

Example:

```php
$this->json(
    [
        'data' => $resource,
    ],
    status: 200
);
```

Errors use:

```php
$this->jsonError(
    code: 'not_found',
    message: 'Article not found.',
    status: 404
);
```

## Current boundaries

The API does not currently provide:

```text
bearer-token authentication
write operations
automatic model serialization
pagination metadata
content negotiation
API version routing
rate limiting
CORS configuration
JSON validation errors
an OpenAPI document
```

Machine-to-machine authentication and write operations remain future work.

The governing rule is:

```text
Expose resources deliberately.
Select fields explicitly.
Apply access rules inside each endpoint.
Keep the current API read-only.
```