<main>
    <section>
        <h1><?php echo $title; ?> <span class="hilite"><?php echo $data->data[0]->username; ?></span></h1>
        <?php partial('messages'); ?>

        <?php component('badge',  $data->data[0]->role,  ['role' => $data->data[0]->role]); ?>
        <?php component('badge', $data->data[0]->status, ['status' => $data->data[0]->status]); ?>

        <form method="post" action="/user/save" class="small validate" novalidate enctype="multipart/form-data">
            <?php csrf(); ?>
            <?php idfield($data->data[0]->id); ?>
            <div class="form-content">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required value="<?php echo $data->data[0]->username; ?>">
                    <div class="user-invalid-error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required value="<?php echo $data->data[0]->email; ?>">
                    <div class="user-invalid-error"></div>
                </div>



                <div class="form-group">
                    <label for="role">Role</label>
                    <select name="role" id="role" required>
                        <option value="default">Choose role...</option>
                        <option value="1" <?php echo ($data->data[0]->role=='admin' ? 'selected' : ''); ?>>Administrator</option>
                        <option value="2" <?php echo ($data->data[0]->role=='editor' ? 'selected' : ''); ?>>Editor</option>
                        <option value="3" <?php echo ($data->data[0]->role=='user' ? 'selected' : ''); ?>>User</option>
                    </select>
                    <div class="user-invalid-error"></div>
                </div>

                <div class="form-group">
                    <label for="status-active">Active</label>
                    <input type="radio" name="status" id="status-active" required value="<?php echo STATUS_ACTIVE; ?>" <?php if ($data->data[0]->status == 'active') echo 'checked'; ?>>
                </div>
                <div class="form-group">
                    <label for="status-inactive">Inactive</label>
                    <input type="radio" name="status" id="status-inactive" required value="<?php echo STATUS_INACTIVE; ?>" <?php if($data->data[0]->status == 'inactive') echo 'checked'; ?>>
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