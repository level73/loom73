<main>
    <section>
        <h1>Login</h1>
        <?php partial('messages'); ?>
        <form method="post" action="/user/access" class="small">
            <?php csrf(); ?>
            <div class="form-content">
                <div class="form-group">
                    <label for="username">Username/Email</label>
                    <input type="text" name="username" id="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password">
                </div>
            </div>
            <div class="form-footer">
                <a href="/user/recover" title="Lost password recovery link">I lost my password</a>
                <button type="submit">Login</button>
            </div>
        </form>
    </section>
</main>
