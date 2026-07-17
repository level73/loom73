<main>
    <section>
        <h1><?php echo $title; ?> for <span class="hilite"><?php echo $user->data[0]->username; ?></span></h1>

        <?php prettyPrint($avatar->first()); ?>

        <?php $avatarFile = $avatar?->first(); ?>

        <?php if ($avatarFile): ?>
            <img
                    src="/asset/view/<?= htmlspecialchars($avatarFile->uuid, ENT_QUOTES, 'UTF-8') ?>"
                    alt="User avatar"
                    class="avatar"
            >
        <?php endif; ?>


        <?php partial('messages'); ?>
        <form method="post" action="/user/profile-update" class="small validate" novalidate enctype="multipart/form-data">
            <?php csrf(); ?>

            <div class="form-content">
                <div class="form-group">
                    <label for="avatar">Avatar</label>
                    <input
                            type="file"
                            id="avatar"
                            name="avatar"
                            accept="image/jpeg,image/png,image/webp"
                    >
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required value="<?php echo $user->data[0]->username; ?>">
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required value="<?php echo $user->data[0]->email; ?>">
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" minlength="8" data-password>
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" minlength="8"  data-password-confirm>
                    <div class="user-invalid-error"></div>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit">Save <i class="icon save"></i></button>
            </div>
        </form>
    </section>
</main>