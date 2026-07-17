/**
 * Loom73Forms
 * Frontend form validation utilities.
 *
 * Usage:
 *   Loom73Forms.init();
 *
 * Markup:
 *   <form class="validate">
 *      ...
 *   </form>
 */

export const Loom73Forms = {

    messages: {
        mandatory: 'This field is required. ',
        invalidEmail: 'This email is invalid. Please check your email address and retry. ',

        tooLong(field) {
            return 'This is too long, the field accepts maximum ' + field.getAttribute('maxlength') + ' characters. ';
        },

        tooShort(field) {
            return 'This is too short, it needs to be at least ' + field.getAttribute('minlength') + ' characters long. ';
        },

        passwordsDontMatch: 'The passwords don\'t match. '
    },

    init(options = {}) {
        this.messages = {
            ...this.messages,
            ...(options.messages || {})
        };

        const forms = document.querySelectorAll('form.validate');

        if (!forms.length) return;

        forms.forEach(form => {
            this.initForm(form);
        });
    },

    initForm(form) {
        form.setAttribute('novalidate', '');

        form.addEventListener('submit', event => {
            event.preventDefault();

            const isValid = this.validateForm(form);

            if (!isValid) {
                this.focusFirstInvalidField(form);
                return;
            }

            form.submit();
        });

        form.addEventListener('input', event => {
            const field = event.target;

            if (!this.isValidatableField(field)) return;

            this.clearFieldError(field);
            this.validatePasswordConfirmation(form);
        });

        form.addEventListener('change', event => {
            const field = event.target;

            if (!this.isValidatableField(field)) return;

            this.clearFieldError(field);
            this.validatePasswordConfirmation(form);
        });
    },

    validateForm(form) {
        let isValid = true;

        this.validatePasswordConfirmation(form);

        const fields = form.querySelectorAll('input, select, textarea');

        fields.forEach(field => {
            if (!this.isValidatableField(field)) return;

            const fieldIsValid = this.validateField(field);

            if (!fieldIsValid) {
                isValid = false;
            }
        });

        return isValid;
    },

    validateField(field) {
        const errorMessage = this.getFieldErrorMessage(field);

        if (!field.validity.valid || errorMessage.length > 0) {
            this.showFieldError(field, errorMessage);
            return false;
        }

        this.clearFieldError(field);
        return true;
    },

    getFieldErrorMessage(field) {
        let errorMessage = '';

        if (field.validity.valueMissing) {
            errorMessage += this.messages.mandatory;
        }

        if (field.validity.typeMismatch && field.type === 'email') {
            errorMessage += this.messages.invalidEmail;
        }

        if (field.validity.tooLong) {
            errorMessage += this.messages.tooLong(field);
        }

        if (field.validity.tooShort) {
            errorMessage += this.messages.tooShort(field);
        }

        if (field.validity.customError && field.dataset.error === 'password-mismatch') {
            errorMessage += this.messages.passwordsDontMatch;
        }

        return errorMessage;
    },

    validatePasswordConfirmation(form) {
        const password = form.querySelector('[data-password]');
        const confirmation = form.querySelector('[data-password-confirm]');

        if (!password || !confirmation) return;

        const passwordsDoNotMatch =
            password.value.length > 0 &&
            confirmation.value.length > 0 &&
            password.value !== confirmation.value;

        if (passwordsDoNotMatch) {
            confirmation.dataset.error = 'password-mismatch';
            confirmation.setCustomValidity('passwordMismatch');
        } else {
            delete confirmation.dataset.error;
            confirmation.setCustomValidity('');
        }
    },

    showFieldError(field, message) {
        const errorElement = this.getErrorElement(field);

        field.setAttribute('aria-invalid', 'true');

        if (!errorElement) return;

        errorElement.textContent = message;
        errorElement.dataset.activeError = 'true';

        if (errorElement.id) {
            field.setAttribute('aria-describedby', errorElement.id);
        }
    },

    clearFieldError(field) {
        const errorElement = this.getErrorElement(field);

        field.setCustomValidity('');
        field.removeAttribute('aria-invalid');

        if (!errorElement) return;

        errorElement.textContent = '';
        delete errorElement.dataset.activeError;

        if (
            errorElement.id &&
            field.getAttribute('aria-describedby') === errorElement.id
        ) {
            field.removeAttribute('aria-describedby');
        }
    },

    getErrorElement(field) {
        const wrapper = field.closest('.form-group, .field, .input-group') || field.parentElement;

        if (!wrapper) return null;

        return wrapper.querySelector('.user-invalid-error');
    },

    focusFirstInvalidField(form) {
        const firstInvalidField = form.querySelector('[aria-invalid="true"]');

        if (firstInvalidField && typeof firstInvalidField.focus === 'function') {
            firstInvalidField.focus();
        }
    },

    isValidatableField(field) {
        if (!field) return false;

        const validTags = ['INPUT', 'SELECT', 'TEXTAREA'];

        if (!validTags.includes(field.tagName)) return false;
        if (field.disabled) return false;
        if (field.type === 'hidden') return false;
        if (field.type === 'submit') return false;
        if (field.type === 'button') return false;
        if (field.type === 'reset') return false;

        return true;
    }

};