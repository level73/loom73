<header>
    <nav class="navbar">
        <a href="/" class="navbar-brand" aria-label="Loom73 Logo - Back to homepage">
            <img src="/assets/loom73.svg" width="32" height="32" alt="Loom73 Logo - Back to homepage">
            Loom73
        </a>
        <ul>
            <li class="nav-item">What is it</li>
            <li class="nav-item">Docs</li>
            <li class="nav-item">Contact</li>
        </ul>
        <?php if(isset($user) && $is_authenticated === true): ?>
        <div class="user-info">
            hello, <span class="username"><?php echo $user->data[0]->username; ?></span>
            <div id="toolbar">
                <button class="button" popovertarget="settings-menu"><span class="sr-only">Settings</span> <i class="icon cog"></i></button>
                <a class="button" href="/user/logout"><span class="sr-only">Logout</span> <i class="icon logout"></i></a>
            </div>
        </div>

        <div id="settings-menu" class="popover-menu" popover="auto">
            <ul>
                <li class="menu-item"><a href="/user/list">All Users</a></li>
                <li class="menu-item"><a href="/user/create">Add a new User</a></li>
                <li class="menu-item"><a href="/user/edit">Edit your Profile</a></li>
            </ul>
        </div>

        <?php endif; ?>

    </nav>
</header>




