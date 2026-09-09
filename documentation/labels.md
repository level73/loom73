# Labels and User Feedback

Loom73 keeps reusable interface labels and feedback messages in:

```text
config/labels.php
```

This file contains application constants that need a stable name and may be reused across controllers or views.

It is not necessary to move every sentence of interface copy into the label file. Page-specific text can remain in the relevant view. Shared messages and recurring labels should be centralized.

## Message constants

Feedback messages use the `MSG_` prefix:

```php
MSG_QUERY_FAIL
MSG_QUERY_SUCCESS
MSG_DUPLICATE_ENTRY
MSG_ACCESS_USER_NOT_FOUND
MSG_PROFILE_UPDATE_FAILED
MSG_PROFILE_UPDATE_SUCCESS
```

Names should describe the event and, when relevant, its outcome:

```text
MSG_{AREA}_{ACTION}_{OUTCOME}
```

Examples:

```php
MSG_ACCESS_SUCCESS
MSG_RECOVERY_EMAIL_SENT
MSG_AVATAR_UPLOAD_FAILED
```

Avoid vague names:

```php
MSG_ERROR
MSG_SUCCESS
MSG_PROBLEM
```

They provide no context when encountered in a controller.

## Shared UI labels

Reusable interface labels may use a descriptive `UI_` prefix:

```php
UI_NAVIGATION
UI_SAVE
UI_CANCEL
```

Collections of related labels may be grouped in a constant array when that makes their purpose clearer.

The current title collection uses:

```php
MSG_TITLES
```

with keys such as:

```text
H_EDIT_PROFILE
H_CREATE_USER
H_USER_LOGIN
H_USER_LIST
```

When this collection is revised, its constant name should reflect that it contains headings rather than feedback messages.

## Flash messages

Controller redirect helpers accept a structured message:

```php
$this->redirectWithSuccess('/user/profile', [
    'message' => MSG_PROFILE_UPDATE_SUCCESS,
    'data' => null,
    'error' => null,
]);
```

The common structure is:

```text
message
    text intended for the user

data
    optional contextual data

error
    optional diagnostic detail
```

User-facing text must remain understandable without the diagnostic fields.

Detailed exception data should only be displayed when development debugging is enabled.

## Writing messages

Messages should:

```text
say what happened
use plain language
avoid exposing internal implementation details
avoid blaming the user
offer a next step when one is useful
```

Good:

```text
Profile updated successfully.
The recovery code has expired.
We could not upload the avatar.
```

Less useful:

```text
Operation failed.
Invalid input.
Database error 1062.
```

Security-sensitive flows may deliberately avoid revealing whether a particular account exists. Password recovery is a common example.

## Dynamic data

Do not construct a large number of constants containing user or domain data.

Keep the stable message in the label file and supply the dynamic context in the controller or view.

Labels are trusted application strings. Values inserted into them may still require escaping according to their output context.

## Language

The baseline label file should use one consistent language.

Application forks may replace or reorganize the labels according to their localization needs. Loom73 does not currently provide a translation framework.

Avoid leaving mixed-language messages in the public baseline.

## Naming rule

```text
MSG_
    feedback or application event message

UI_
    reusable interface label

descriptive area/action/outcome
    enough context to understand the constant without opening the file
```

The purpose of the convention is fast recognition, not a complex taxonomy.
