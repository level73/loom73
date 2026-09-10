# Heddle

Heddle provides database-backed sessions, authentication state, roles and abilities.

```text
Session  connect a PHP session token to a user
Auth     expose authentication and authorization checks
```

Heddle does not validate login credentials. The application verifies the submitted username and password, then asks `Auth` to authorize the identified user.

## Required configuration

Heddle uses:

```env
APPNAME='Example'
SESSION_KEY=''
SESSION_SALT=''
```

The public bootstrap calls:

```php
session_name($_SERVER['APPNAME']);
session_start();
```

PHP session-cookie security settings remain the responsibility of PHP and server configuration. Production installations should configure HTTPS-only, HttpOnly and appropriate SameSite behavior.

## Session

`Loom73\Heddle\Session` extends Beam's `Model` and uses:

```text
auth_session
idauth_session
```

### Creating a session record

When a user account is created:

```php
$Session = new Session();

$result = $Session->createSession($userId);
```

This creates a placeholder database token prefixed with:

```text
$noToken$
```

### Authorizing a browser session

`Auth::authorize()` generates the active token and delegates to:

```php
$Session->setSession(
    $userId,
    $sessionToken
);
```

On a successful database update, the same token is stored in:

```php
$_SESSION[$_SERVER['APPNAME']][$_SERVER['SESSION_KEY']]
```

### Resolving the current user

The protected `getSession()` joins:

```text
auth_user
auth_session
auth_role
```

and returns a profile containing:

```text
id
username
email
role_id
role
```

A missing PHP session key returns a successful but empty `QueryResult`.

## Auth

The base controller creates `Auth` and resolves the current user during construction.

Application code can also use it directly:

```php
$Auth = new Auth();

if ($Auth->isLoggedIn()):
    $profile = $Auth->getProfile()->first();
endif;
```

### Public methods

| Method | Purpose |
| --- | --- |
| `isLoggedIn()` | Resolve the current session and report whether it contains a profile. |
| `authorize(int $userId)` | Generate, store and activate a session token. |
| `getProfile()` | Return the cached or newly resolved profile as `QueryResult`. |
| `hasRole(?object $user, string\|array $roles)` | Check one or more role names. |
| `isAdmin(?object $user)` | Check the `admin` role. |
| `can(?object $user, string $ability)` | Check the role mapping for an ability. |
| `logout()` | Clear PHP session state and destroy the current session. |

Role methods accept either a profile object or a `QueryResult` containing a profile.

## Authentication flow

A normal login action follows this sequence:

```text
POST and CSRF validation
    ↓
find user by username or email
    ↓
check account state
    ↓
verify password
    ↓
Auth::authorize(user ID)
    ↓
record auth.login
    ↓
redirect
```

The current `UserCtrl` verifies passwords before calling Heddle.

Successful authorization means the database session update completed. Applications should inspect the returned `QueryResult` before continuing.

## Roles and abilities

Current roles are:

```text
admin
editor
user
```

Current abilities are defined inside `Auth`:

```php
'manage_users' => ['admin'];
'edit_content' => ['admin', 'editor'];
'view_content' => ['admin', 'editor', 'user'];
```

Examples:

```php
if ($Auth->hasRole($profile, ['admin', 'editor'])):
    // Role is allowed.
endif;

if ($Auth->can($profile, 'edit_content')):
    // Ability is allowed.
endif;
```

Unknown abilities return `false`.

The base controller exposes explicit guards:

```php
$this->requireAuth();
$this->requireAdmin();
$this->requireEditor();
$this->requireAbility('edit_content');
```

Abilities are currently a private in-code map. Adding an ability requires changing `Auth`.

## Logout

```php
$Auth->logout();
```

The current implementation:

1. replaces the complete `$_SESSION` array with an empty array;
2. destroys the active PHP session.

It does not delete or clear the corresponding token stored in `auth_session`. That row may later be replaced by another successful authorization.

## Current boundaries

Heddle currently provides session authentication for the Loom73 web application.

It does not currently provide:

```text
API bearer tokens
refresh tokens
remember-me cookies
multi-device session management
session expiry stored in the database
session listing or revocation
configurable ability providers
```

The current session lookup does not filter users by their active status and does not apply database-token expiration. Applications requiring immediate revocation for deactivated accounts must add that condition to the session query.

Authentication identifies the user. Controllers still decide whether that user may perform a particular action.