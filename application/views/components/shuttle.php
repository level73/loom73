<main id="main-content">
    <section class="grid grid-1_2-col">

        <div>
            <h1>Shuttle</h1>
            <hr />
            <p class="intro">The command-line tool for <strong>Loom<span class="accent">73</span></strong>.</p>
            <p>Install, inspect and maintain a Loom73 instance from the CLI.</p>
            <p>Small tools. Clear conventions. No unnecessary machinery.</p>
            <ul class="checklist">
                <li>Install runtime state</li>
                <li>Inspect versions and instance health</li>
                <li>Create admin users</li>
                <li>Built for deployed instances</li>
            </ul>
        </div>
        <div>
            <figure>
                <img width="1672" height="941" src="/assets/raster/shuttle.webp" class="image" alt="A wooden shuttle from an 1800s loom.">
                <figcaption>The Shuttle</figcaption>
            </figure>
        </div>
    </section>
    <section class="grid breakout grid-25">
        <div class="grid grid-1_2-col with-gap">
            <span class="stitch stitch--cli x6 color-accent" aria-hidden="true"></span>
            <div>
                <h4>Health & Info</h4>
                <p>Check Loom73 instance health, such as PHP versions, database connections, runtime directory permissions and more.</p>
            </div>
        </div>
        <div class="grid grid-1_2-col with-gap">
            <span class="stitch stitch--download x6 color-accent" aria-hidden="true"></span>
            <div>
                <h4>Clean install</h4>
                <p>Install the schema, basic seeds and the runtime directories.</p>
            </div>
        </div>
        <div class="grid grid-1_2-col with-gap">
            <span class="stitch stitch--user x6 color-accent" aria-hidden="true"></span>
            <div>
                <h4>Admin bootstrap</h4>
                <p>Create the first administrative user.</p>
            </div>
        </div>
        <div class="grid grid-1_2-col with-gap">
            <span class="stitch stitch--puzzle x6 color-accent" aria-hidden="true"></span>
            <div>
                <h4>Extensible by convention</h4>
                <p>Add new commands with one file, one class, one constructor-driven operation.</p>
            </div>
        </div>
    </section>

    <section class="grid grid-2-1-1-col with-gap">
        <div>
            <h2>Core commands</h2>
            <hr>
            <table id="shuttle-commands">
                <thead>
                    <tr>
                        <th>Command</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td>php shuttle loom73.info</td>
                    <td>Instance version and health</td>
                </tr>
                <tr>
                    <td>php shuttle loom73.install</td>
                    <td>Perform a clean installation</td>
                </tr>
                <tr>
                    <td>php shuttle user.admin</td>
                    <td>Create admin users</td>
                </tr>
                <tr>
                    <td>php shuttle asset_type.new</td>
                    <td>Create a new asset type entry</td>
                </tr>
                <tr>
                    <td>php shuttle ledger.cleanup</td>
                    <td>Garbage collection of audit trail data</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div>
            <h2>Typical first install</h2>
            <hr>
            <div class="code-snippet">
                cd /var/www/example.com<br />
                php shuttle loom73.install<br />
                php shuttle user.admin
            </div>
        </div>
        <div>
            <h2>loom73.install ops</h2>
            <hr>
            <ul class="checklist small">
                <li>database tables</li>
                <li>seed data</li>
                <li>runtime dirs
                    <ul>
                        <li>storage/cache</li>
                        <li>storage/logs</li>
                        <li>storage/uploads</li>
                    </ul>
                </li>
            </ul>
        </div>
    </section>
</main>