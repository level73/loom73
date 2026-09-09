# Forms

Loom73 Forms adds accessible frontend feedback to the browser constraint validation API.

The module validates forms marked with the `validate` class:

```html
<form class="validate" method="post" action="/contact">
    <!-- fields -->
</form>
```

`Loom73Forms.init()` is called automatically by the default frontend entry point.

## Smallest working example

Each field should have its own wrapper and error element:

```html
<form class="validate" method="post" action="/contact">
    <div class="form-group">
        <label for="contact-name">Name</label>

        <input
            type="text"
            id="contact-name"
            name="name"
            minlength="2"
            maxlength="40"
            autocomplete="name"
            required
        >

        <div
            class="user-invalid-error"
            id="contact-name-error"
            aria-live="polite"
        ></div>
    </div>

    <div class="form-group">
        <label for="contact-email">Email</label>

        <input
            type="email"
            id="contact-email"
            name="email"
            maxlength="120"
            autocomplete="email"
            required
        >

        <div
            class="user-invalid-error"
            id="contact-email-error"
            aria-live="polite"
        ></div>
    </div>

    <button type="submit">Send</button>
</form>
```

The module adds `novalidate` during initialization. It should not normally be added manually: without JavaScript, leaving it out allows native browser validation to remain available.

## Validation behavior

On submission, Loom73:

1. checks the password confirmation pair, when present;
2. validates enabled `input`, `select` and `textarea` elements;
3. writes supported messages into the corresponding error element;
4. sets `aria-invalid="true"` on invalid fields;
5. associates the field with the error through `aria-describedby`;
6. focuses the first invalid field;
7. prevents submission when the form is invalid.

When the form is valid, Loom73 does not prevent submission.

Hidden, disabled, submit, reset and ordinary button controls are excluded from validation.

## Error elements

The module looks for `.user-invalid-error` inside the field's nearest:

```text
.form-group
.field
.input-group
```

If none of these wrappers exists, it searches the field's direct parent.

Use one wrapper and one error element for each field:

```html
<div class="form-group">
    <label for="username">Username</label>
    <input id="username" name="username" required>
    <div
        class="user-invalid-error"
        id="username-error"
        aria-live="polite"
    ></div>
</div>
```

An error element with an `id` allows Loom73 to set:

```html
aria-describedby="username-error"
```

The current implementation assigns the error ID directly. If a field already requires other `aria-describedby` references, the form module must be extended to preserve and combine them.

## Supported messages

The current message map provides feedback for:

- missing required values;
- invalid email syntax;
- values exceeding `maxlength`;
- values shorter than `minlength`;
- mismatched passwords.

Other native constraint states can still make a field invalid, but the current message map does not provide custom text for every browser validity state, such as `patternMismatch`, range errors or step errors.

Add a matching message before relying on one of those constraints for custom feedback.

## Password confirmation

Place the two attributes inside the same form:

```html
<div class="form-group">
    <label for="password">Password</label>
    <input
        type="password"
        id="password"
        name="password"
        minlength="8"
        autocomplete="new-password"
        data-password
        required
    >
    <div
        class="user-invalid-error"
        id="password-error"
        aria-live="polite"
    ></div>
</div>

<div class="form-group">
    <label for="password-confirm">Confirm password</label>
    <input
        type="password"
        id="password-confirm"
        name="password_confirm"
        minlength="8"
        autocomplete="new-password"
        data-password-confirm
        required
    >
    <div
        class="user-invalid-error"
        id="password-confirm-error"
        aria-live="polite"
    ></div>
</div>
```

A mismatch is reported on the confirmation field when both values are non-empty and different.

## Form events

Every validated submission dispatches one bubbling custom event:

```text
loom73:form-valid
loom73:form-invalid
```

An application can use them for additional interface feedback:

```js
const form = document.querySelector('#contact-form');
const status = document.querySelector('#contact-form-status');

form.addEventListener('loom73:form-valid', () => {
    status.textContent = 'The form is valid.';
});

form.addEventListener('loom73:form-invalid', () => {
    status.textContent = 'Please correct the highlighted fields.';
});
```

A demonstration form that must never submit can prevent its normal submission separately:

```js
form.addEventListener('submit', event => {
    event.preventDefault();
});
```

The custom events report validation state. They do not submit data, perform an asynchronous request or display a success message automatically.

## Custom messages

Default messages are defined on `Loom73Forms.messages`.

A fork can configure them in `frontend-src/js/index.js`:

```js
Loom73Forms.init({
    messages: {
        mandatory: 'This field is required.',
        invalidEmail: 'Enter a valid email address.',
        tooShort(field) {
            return `Enter at least ${field.minLength} characters.`;
        },
        tooLong(field) {
            return `Enter no more than ${field.maxLength} characters.`;
        },
        passwordsDontMatch: 'The passwords do not match.'
    }
});
```

Rebuild the frontend after changing the source.

Initialize the form module once. Calling it again attaches another set of listeners to forms already initialized.

## Server validation

Frontend validation improves feedback but does not establish trust.

Controllers must validate submitted values again before using them, writing them to the database or passing them to another service.
