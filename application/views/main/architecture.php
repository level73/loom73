<main id="main-content">
    <section class="grid grid-1_2-col">
        <div>
            <h1>Architecture</h1>
            <hr>
            <p class="intro">A closer look at the structure and conventions that make <strong>Loom<span class="accent">73</span></strong> clear and maintainable.</p>
        </div>
        <div>
            <figure>
                <img  width="1672" height="941" class="img-fluid" src="/assets/raster/medieval-loom-scheme.webp" alt="A schematic depiction of a medieval loom. Main components are labeled and explained, particular attention to the shuttle element.">
                <figcaption>A schematic depiction of a medieval loom.</figcaption>
            </figure>
        </div>
    </section>

    <section class="grid breakout">
        <div>
            <h2>Core Modules</h2>
            <hr>
            <p>Each module has a single responsibility and can be used independently where appropriate.</p>

            <div class="grid grid-4-col with-gap">
                <div class="card architecture-card">
                    <h3>Woodframe</h3>
                    <p>Blueprint structure, configuration, shared utilities and registries.</p>
                </div>
                <div class="card architecture-card">
                    <h3>Beam</h3>
                    <p>Persistence primitives, database access and query results.</p>
                </div>
                <div class="card architecture-card">
                    <h3>Heddle</h3>
                    <p>Authentication, sessions, roles and abilities.</p>
                </div>
                <div class="card architecture-card">
                    <h3>Yarn</h3>
                    <p>Asset management, ownership, slots and visibility.</p>
                </div>
                <div class="card architecture-card">
                    <h3>Ledger</h3>
                    <p>User accountability and audit trail.</p>
                </div>
                <div class="card architecture-card">
                    <h3>Weave</h3>
                    <p>Application orchestration, controllers and API delivery.</p>
                </div>
                <div class="card architecture-card">
                    <h3>Shuttle</h3>
                    <p>Installation, maintenance and CLI tooling.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid">
        <header>
            <h2>Request flow</h2>
            <hr>
            <p>A typical request in a Loom73 application.</p>
        </header>
        <div class="flow-diagram">
            <div class="flow-diagram-element">
                Request
            </div>
            <div class="stitch stitch--arrow x2"></div>
            <div class="flow-diagram-element">
                Routing
            </div>
            <div class="stitch stitch--arrow x2"></div>
            <div class="flow-diagram-element">
                Controller
            </div>
            <div class="stitch stitch--arrow x2"></div>
            <div class="flow-diagram-element">
                Model
            </div>
            <div class="stitch stitch--arrow x2"></div>
            <div class="flow-diagram-element">
                Template
            </div>
            <div class="stitch stitch--arrow x2"></div>
            <div class="flow-diagram-element">
                Response
            </div>
        </div>
    </section>
    <section>
        <div class="grid-2-col grid with-gap">


            <div>
                <h2 class="balance">Conventions</h2>
                <hr>
                <ul id="conventions" class="list large">
                    <li>Controllers in <span class="code">/application/controllers</span></li>
                    <li>Models in <span class="code">/application/models</span></li>
                    <li>Views in <span class="code">/application/views</span></li>
                    <li>Assets managed by <span class="code">Loom73\Yarn</span></li>
                    <li>Runtime data in <span class="code">/storage/*</span></li>
                </ul>
            </div>
            <figure>
                <img width="400" height="400" src="/assets/mvc.svg" loading="lazy" class="image" alt="Diagram of the MVC architecture">
                <figcaption>Model - View - Controller</figcaption>
            </figure>
        </div>
    </section>
</main>