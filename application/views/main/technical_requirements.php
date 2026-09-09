<main id="main-content">
    <section class="grid grid-1_2-col">
        <div>
            <h1>Technical</h1>
            <hr />
            <p class="intro">
                Technical details, requirements and technology choices.
            </p>
        </div>
        <figure>
            <img  width="1672" height="941" src="/assets/raster/tech-spec.webp" class="image" alt="Ink drawing of the various components needed to assemble a medieval loom.">
            <figcaption>The various parts that compose a loom.</figcaption>
        </figure>
    </section>
    <section class="grid grid-3-col with-gap breakout">
        <div class="">
            <h2>Runtime requirements</h2>
            <hr />
            <ul class="list large">
                <li>PHP 8.3 or newer</li>
                <li>PDO, PDO MySQL, Fileinfo, OpenSSL</li>
                <li>MySQL or MariaDB</li>
                <li>Webserver (Apache compatible)</li>
            </ul>
        </div>
        <div class="">
            <h2>PHP dependencies</h2>
            <hr />
            <ul class="list large">
                <li>vlucas/phpdotenv</li>
                <li>PHPMailer</li>
            </ul>
        </div>
        <div>
            <h2>No frontend frameworks</h2>
            <hr />
            <ul class="list large">
                <li>No React, Vue, Angular, jQuery, Bootstrap, Tailwind...</li>
                <li>Modern CSS</li>
                <li>Vanilla JS</li>
            </ul>
        </div>
    </section>
    <section class="grid grid-2-col">
        <div>
            <h2>Build tooling (Development only)</h2>
            <hr />
            <p>Node.js, npm and Grunt are used during development and deployment for asset optimization.</p>
            <p>Including SVG, CSS and JS minification, as well as WEBP conversion for raster images.</p>

            <div class="flow-diagram">
                <div class="flow-diagram-element">
                    Source
                </div>
                <div class="stitch stitch--arrow x2"></div>
                <div class="flow-diagram-element">
                    Build
                </div>
                <div class="stitch stitch--arrow x2"></div>
                <div class="flow-diagram-element">
                    Optimized assets
                </div>
            </div>
        </div>
        <div>
            <h2>Deployment</h2>
            <hr />
            <p>Deploy code, install runtime state. Never overwrite instance data during core updates</p>
            <ul class="list large">
                <li>GitHub actions</li>
                <li>Composer install</li>
                <li>Build frontend assets</li>
                <li>rsync deployment</li>
            </ul>

            <a class="button hollow">View the docs <i class="stitch stitch--arrow" aria-hidden="true"></i></a>
        </div>

    </section>
</main>