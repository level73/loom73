<main>
    <section>
        <h1><?php echo $title; ?></h1>
        <?php partial('messages'); ?>
        <form method="post" action="/user/recover-send" class="small">
            <?php csrf(); ?>
            <div class="form-content">
                <div class="form-group">
                    <label for="username">Username/Email</label>
                    <input type="text" name="username" id="username" required>
                </div>
            </div>
            <div class="form-footer">
                <button type="submit">Send me the recovery link <i class="icon mail"></i></button>
            </div>
        </form>
    </section>
</main>
