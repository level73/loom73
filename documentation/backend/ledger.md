# Ledger

Ledger records meaningful application actions.

It answers:

```text
who did what
to which application entity
when it happened
with which useful context
```

Ledger is an audit trail. Operational errors belong to `Woodframe\Logger`.

## Configuration

Ledger reads:

```text
config/modules/ledger.php
```

```php
return [
    'enabled' => true,
    'retention' => 5,
];
```

`retention` is expressed in days.

A retention value of `0` disables age-based cleanup.

Explicit purge remains available independently from the retention
policy:

```console
php shuttle ledger.cleanup --purge
php shuttle ledger.cleanup --purge --force
```

## Recording an event

```php
use Loom73\Ledger\Ledger;

$Ledger = new Ledger();

$recorded = $Ledger->record(
    actorId: $userId,
    action: 'article.update',
    ownerType: 'article',
    ownerId: (string) $articleId,
    summary: 'Article updated',
    metadata: [
        'updated_fields' => [
            'title',
            'status',
        ],
    ]
);
```

Anonymous actions use a null actor:

```php
$Ledger->record(
    actorId: null,
    action: 'auth.recover',
    ownerType: 'user',
    ownerId: (string) $userId,
    summary: 'Password recovery requested'
);
```

Controllers can use the wrappers described in [Woodframe](woodframe.md#ledger-helpers).

## Event rules

A valid event requires:

| Field | Rule |
| --- | --- |
| `actorId` | `null` or an integer greater than zero. |
| `action` | Lowercase dotted identifier, maximum 120 characters. |
| `ownerType` | Registered in `OwnerRegistry`, maximum 100 characters. |
| `ownerId` | Non-empty, maximum 100 characters. |
| `summary` | Non-empty, maximum 255 characters. |
| `metadata` | JSON-serializable array. |

Valid actions include:

```text
auth.login
auth.logout
auth.password_reset
user.create
user.update
asset.upload
article.publish
```

An action must contain at least two dot-separated segments.

## Return behavior

`Ledger::record()` returns:

```text
true
    event persisted

false
    disabled, invalid or unable to persist
```

Validation, serialization and database failures are logged through `Logger`.

Ledger deliberately catches its own operational failures. A failed audit write should not reverse a successful user action.

Applications may decide that a specific regulated workflow requires a stricter transaction boundary, but that is outside the default Ledger contract.

## Stored data

A Ledger event can contain:

```text
actor user ID
action
owner type
owner ID
summary
JSON metadata
IP address
user agent
creation time
```

IP addresses are truncated to 45 characters. User agents are truncated to 255 characters.

`REMOTE_ADDR` is recorded directly. Deployments behind a trusted proxy must define their own policy before using forwarded headers.

Metadata should contain identifiers and useful change summaries. Do not record passwords, authentication tokens, recovery codes, private document contents or unnecessary personal data.

## Retention

`LedgerEvent::deleteByRetentionPolicy()` deletes events older than the configured retention period.

`LedgerEvent::purge()` deletes every event.

The Shuttle interface is:

```console
php shuttle ledger.cleanup
php shuttle ledger.cleanup --force
php shuttle ledger.cleanup --purge
php shuttle ledger.cleanup --purge --force
```

Interactive cleanup requests confirmation. Non-interactive or scheduled execution requires `--force`.

When retention is `0`, the current command exits without cleanup. This currently also prevents purge through the command and should be aligned before documenting `--purge` as independent of retention.

## Boundaries

Ledger does not currently provide:

```text
a browser viewer
search and filtering UI
automatic event discovery
remote telemetry
immutable external audit storage
cryptographic signing
```

Application actions must be recorded explicitly.
