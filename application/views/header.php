<header id="main-header" data-responsive-nav>

        <a href="/" class="navbar-brand" aria-label="Loom73 Logo - Back to homepage">
            <img src="/assets/loom73.svg" width="32" height="32" alt="Loom73 Logo - Back to homepage">
            Loom73
        </a>


        <button class="navbar-toggler" type="button" aria-controls="main-navigation" aria-expanded="false" data-nav-toggle>
            <i class="stitch stitch--menu color-text x1.5" aria-hidden="true"></i>
            <span class="sr-only">Menu</span>
        </button>
        <nav class="navbar" id="main-navigation" aria-label="Main navigation" data-nav-panel>
            <ul id="navigation-menu" class="navbar-menu">
                <li class="nav-item"><a href="/">Overview</a></li>
                <li class="nav-item"><a href="/main/architecture">Architecture</a></li>
                <li class="nav-item">
                    <a href="/components">Components <i class="stitch stitch--caret" aria-hidden="true"></i></a>
                    <ul class="sub-nav">
                        <li class="nav-item"><a href="/components/shuttle">Shuttle</a></li>
                        <li class="nav-item"><a href="/components/stitch-icons">Stitch Icons</a></li>
                        <li class="nav-item"><a href="/components/tables">Tables</a></li>
                        <li class="nav-item"><a href="/components/forms">Forms</a></li>
                        <li class="nav-item"><a href="/components/ui-utilities">UI Utilities</a></li>

                    </ul>
                </li>
                <li class="nav-item"><a href="/main/technical-requirements">Tech</a></li>
                <li class="nav-item">Docs</li>
                <li class="nav-item">Github</li>
            </ul>
        </nav>
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
                <li class="menu-item"><a href="/user/profile">Edit your Profile</a></li>
            </ul>
        </div>

        <?php endif; ?>

    </nav>
</header>




