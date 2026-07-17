const Loom73 = {

    version: '6.0.0',
    consoleCSS: 'background: #222222; color: #57D2A3; font-weight: bold;',
    formValidationMessages: {
        mandatory: 'This field is required. ',
        invalidEmail: 'This email is invalid. Please check your email address and retry. ',
        tooLong: function(a){ // "a" is the field
            return 'This is too long, the field accepts maximum ' + a.getAttribute('maxlength') + ' characters. ';
        },
        tooShort: function(a){ // "a" is the field
            return 'This is too short, it needs to be at least ' + a.getAttribute('minlength') + ' characters long. ';
        },
        passwordsDontMatch: 'The passwords don\'t match. ',
    },

    init: function(){
        console.log( '%c --- Loom73 v. ' + Loom73.version +  ' Initialized ---', Loom73.consoleCSS );
        Loom73.formValidation();
        Loom73.dismissFlash();
        Loom73.tableSorter();
        Loom73.searchTable();
        Loom73.handleModals();
    },

    formValidation: function(){
        const theForm = document.querySelector('form.validate');

        if(theForm){
            theForm.addEventListener("submit", function(e){
                e.preventDefault();
                let validForm = true;
                let theFields = theForm.querySelectorAll('input, select, textarea');

                theFields.forEach( field => {
                    let error_message = '';
                    const user_feedback = field.parentElement.querySelector('.user-invalid-error');

                    if(field.getAttribute('type') === 'password' ){
                        console.log('password-field');

                        if(document.querySelector('input[name="password"]').value !== document.querySelector('input[name="confirm_password"]').value){
                            field.setCustomValidity('passwordMismatch');
                        }
                        else{
                            field.setCustomValidity('');
                        }
                    }


                    if(!field.validity.valid){
                        validForm = false;


                        if(field.validity.valueMissing){
                            error_message += Loom73.formValidationMessages.mandatory;
                        }
                        if(field.validity.typeMismatch && field.getAttribute('type') === 'email'){
                            error_message += Loom73.formValidationMessages.invalidEmail;
                        }
                        if(field.validity.tooLong){
                            error_message += Loom73.formValidationMessages.tooLong(field);
                        }
                        if(field.validity.tooShort){
                            error_message += Loom73.formValidationMessages.tooShort(field);
                        }
                        if(field.validity.customError && field.getAttribute('type') === 'password'){
                            error_message += Loom73.formValidationMessages.passwordsDontMatch;
                        }

                        if(error_message.length > 0){
                            user_feedback.textContent = error_message;
                            user_feedback.dataset.active_error = "true";
                        }
                    }
                    console.log('%c' + field.validity, Loom73.consoleCSS);
                });

                if(!validForm){
                    return false;
                }
                else {
                    e.target.submit();
                }

            });
        }
    },

    handleModals: function(){
        document.addEventListener('click', event => {
            const openButton = event.target.closest('[data-dialog-open]');
            const closeButton = event.target.closest('[data-dialog-close]');
            if (openButton) {
                const dialog = document.getElementById(openButton.dataset.dialogOpen);
                if (dialog && typeof dialog.showModal === 'function') {
                    dialog.showModal();
                }
                return;
            }
            if (closeButton) {
                const dialog = document.getElementById(closeButton.dataset.dialogClose);
                if (dialog && typeof dialog.close === 'function') {
                    dialog.close();
                }
            }
        });
    },

    dismissFlash: function(){
        const dismiss = document.querySelectorAll('.dismiss');
        if(dismiss){
            dismiss.forEach( (dismissButton) => {
                dismissButton.addEventListener('click', (e) => {
                    e.currentTarget.parentNode.remove();
                });
            })
        }
    },


    tableSorter: function(){

            const tables = document.querySelectorAll('[data-sortable-table]');

            if (!tables.length) return;

            tables.forEach(initSortableTable);

            function initSortableTable(table) {
                const buttons = table.querySelectorAll('thead th button[data-sortable]');

                buttons.forEach(button => {
                    const th = button.closest('th');
                    if (!th) return;
                    if (!th.hasAttribute('aria-sort')) {
                        th.setAttribute('aria-sort', 'none');
                    }
                    button.addEventListener('click', () => {
                        sortTable(table, th, button);
                    });
                });
            }

            function sortTable(table, th, button) {
                const tbody = table.querySelector('tbody');
                if (!tbody) return;

                const headerRow = th.parentElement;
                const headers = Array.from(headerRow.children);
                const columnIndex = headers.indexOf(th);
                const type = button.dataset.sortable || 'text';

                const currentSort = th.getAttribute('aria-sort');
                const direction = currentSort === 'ascending' ? 'descending' : 'ascending';

                headers.forEach(header => {
                    if (header.hasAttribute('aria-sort')) {
                        header.setAttribute('aria-sort', 'none');
                    }
                });

                th.setAttribute('aria-sort', direction);

                const rows = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((rowA, rowB) => {
                    const cellA = rowA.children[columnIndex];
                    const cellB = rowB.children[columnIndex];

                    const valueA = getSortableValue(cellA);
                    const valueB = getSortableValue(cellB);

                    const result = compareValues(valueA, valueB, type);

                    return direction === 'ascending' ? result : -result;
                });

                rows.forEach(row => tbody.appendChild(row));
            }

        function getSortableValue(cell) {
            if (!cell) return '';
            if (cell.dataset.sortValue !== undefined) {
                return cell.dataset.sortValue.trim();
            }
            return cell.textContent.trim();
        }

        function compareValues(valueA, valueB, type) {
            if (type === 'number') {
                return toNumber(valueA) - toNumber(valueB);
            }
            if (type === 'date') {
                return toDate(valueA) - toDate(valueB);
            }
            return valueA.localeCompare(valueB, document.documentElement.lang || 'en', {
                sensitivity: 'base',
                numeric: true
            });
        }

        function toNumber(value) {
            const normalized = value
                .replace(/\./g, '')
                .replace(',', '.');

            const number = parseFloat(normalized);
            return Number.isNaN(number) ? 0 : number;
        }

        function toDate(value) {
            const timestamp = new Date(value).getTime();
            return Number.isNaN(timestamp) ? 0 : timestamp;
        }
    },

    searchTable: function(){
        const inputs = document.querySelectorAll('[data-table-search]');

        inputs.forEach(input => {
            const table = document.querySelector(input.dataset.tableSearch);
            if (!table) return;

            const rows = Array.from(table.querySelectorAll('tbody tr'));

            input.addEventListener('input', () => {
                const query = normalize(input.value);

                rows.forEach(row => {
                    const text = normalize(row.textContent);
                    const matches = text.includes(query);

                    row.hidden = query && !matches;
                });
            });
        });

        function normalize(value) {
            return value
                .toLowerCase()
                .normalize('NFD')
                .replace(/\p{Diacritic}/gu, '')
                .trim();
        }
    },


};

document.addEventListener('DOMContentLoaded', () => {
    new Loom73.init();
})
