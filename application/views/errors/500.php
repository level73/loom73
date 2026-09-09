<?php
$isDevelopment =
    ($_SERVER['SYSTEM_STATUS'] ?? 'production')
    === 'development';
?>

    <main id="main-content">
    <section class="grid">
        <header>
            <span class="badge">Error 500</span>
            <h1>Something went wrong</h1>
        </header>
        <div>
            <p>
                The page cannot be displayed at the moment.
            </p>

            <?php if ($isDevelopment && isset($missingView)): ?>
                <pre><?php
                    echo htmlspecialchars(
                        $missingView,
                        ENT_QUOTES,
                        'UTF-8'
                    );
                    ?></pre>
            <?php endif; ?>
        </div>
    </section>
    </main>