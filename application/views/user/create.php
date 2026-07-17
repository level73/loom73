<main>
    <section>
        <h1><?php echo $title; ?></h1>
        <?php partial('messages'); ?>

        <form method="post" action="/user/save" class="small validate" novalidate>
            <?php csrf(); ?>
            <div class="form-content">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                    <div class="user-invalid-error"></div>
                </div>

                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="default">Choose role...</option>
                        <option value="1">Administrator</option>
                        <option value="2">Editor</option>
                        <option value="3">User</option>
                    </select>
                    <div class="user-invalid-error"></div>
                </div>

                <div class="form-group">
                    <label for="status-active">Active</label>
                    <input type="radio" name="status" id="status-active" required value="<?php echo STATUS_ACTIVE; ?>">
                </div>
                <div class="form-group">
                    <label for="status-inactive">Inactive</label>
                    <input type="radio" name="status" id="status-inactive" required value="<?php echo STATUS_INACTIVE; ?>">
                </div>




                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" minlength="8" required data-password>
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" minlength="8" required  data-password-confirm>
                    <div class="user-invalid-error"></div>
                </div>

            </div>
            <div class="form-footer">
                <button type="submit">Save <i class="icon save"></i></button>
            </div>
        </form>
    </section>
</main>