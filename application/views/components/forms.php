<main id="main-content">
    <section class="grid">
        <header>
            <h1>Loom73 Forms <span class="accent">Example</span></h1>
            <hr />
            <p>Try submitting the form with missing or incorrect values to see the frontend validation patterns in action.</p>
            <p>This example validates entirely in the browser and does not send or store any data. Frontend validation must always be repeated on the server in a real application.</p>
        </header>

        <div>
            <form id="frontend-validation-example" class="small validate">
                <fieldset>
                    <legend>Frontend validation example</legend>

                    <div class="form-content">
                        <div class="form-group">
                            <label for="example-name">Name</label>
                            <input type="text" id="example-name" name="name" minlength="2" maxlength="40" autocomplete="name" required>
                            <div class="user-invalid-error" id="example-name-error" aria-live="polite"></div>
                        </div>

                        <div class="form-group">
                            <label for="example-email">Email</label>
                            <input type="email" id="example-email" name="email" maxlength="120" autocomplete="email" required>
                            <div class="user-invalid-error" id="example-email-error" aria-live="polite"></div>
                        </div>

                        <div class="form-group">
                            <label for="example-password">Password</label>
                            <input type="password" id="example-password" name="password" minlength="8" autocomplete="new-password" data-password required>
                            <div class="user-invalid-error" id="example-password-error" aria-live="polite"></div>
                        </div>

                        <div class="form-group">
                            <label for="example-password-confirm">Confirm password</label>
                            <input type="password" id="example-password-confirm" name="password_confirm" minlength="8" autocomplete="new-password" data-password-confirm required>
                            <div class="user-invalid-error" id="example-password-confirm-error" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="form-footer">
                        <button type="submit">Validate form</button>
                    </div>
                </fieldset>
            </form>
            <p id="frontend-validation-status" role="status" aria-live="polite" aria-atomic="true"></p>
        </div>
    </section>
    <section class="grid">
        <div>
            <h2>References</h2>
            <hr />
            <a class="button hollow" href="https://level73.github.io/loom73/frontend/forms/" target="_blank">Read the docs <i class="stitch stitch--arrow"></i></a>
        </div>
    </section>
</main>
<script>
    const exampleForm = document.getElementById('frontend-validation-example');
    const exampleStatus = document.getElementById('frontend-validation-status');

    const clearExampleStatus = () => {
        exampleStatus.textContent = '';
        exampleStatus.classList.remove( 'flash', 'flash--success');
    };

    exampleForm.addEventListener('submit', event => {
        // This is a demo. It should never send data to the server
        event.preventDefault();
    });

    exampleForm.addEventListener( 'loom73:form-valid', () => {
            exampleStatus.classList.add('flash', 'flash--success');
            exampleStatus.textContent = 'Form valid. No data was sent or stored.';
        }
    );

    exampleForm.addEventListener('loom73:form-invalid', clearExampleStatus);
    exampleForm.addEventListener('input', clearExampleStatus);
    exampleForm.addEventListener('change', clearExampleStatus);
</script>