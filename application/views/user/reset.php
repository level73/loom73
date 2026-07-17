<main>
    <section>
        <h1><?php echo $title; ?></h1>
        <?php partial('messages'); ?>
        <form method="post" action="/user/reset-save" class="small">
            <?php csrf(); ?>
            <input type="hidden" name="recovery" value="<?= htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') ?>">
            <?php prettyPrint($user); ?>
            <div class="form-content">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" minlength="8" data-password required>
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" minlength="8"  data-password-confirm required>
                    <div class="user-invalid-error"></div>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit">Save new Password <i class="icon save"></i></button>
            </div>
        </form>
    </section>
</main>
