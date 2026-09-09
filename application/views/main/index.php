<main id="main-content">
    <section class="grid grid-2-col">
        <div class="">
            <h1>Loom<span class="accent">73</span></h1>
            <p class="claim">
                A clear <span class="hilite">foundation</span> for building PHP applications.
            </p>
            <hr>
            <p class="claim">
                Loom73 is a lightweight application blueprint built around explicit conventions, reusable components and architectural patterns woven from years of real-world development experience.
            </p>
            <p class="claim">
                <strong>Not a framework to learn.</strong><br />
                <strong>A <em>foundation</em> to build on.</strong>
            </p>
            <a class="button" href="#">Learn More <span class="stitch stitch--arrow"></span></a> <a class="button hollow" href="#">Read the docs <span class="stitch stitch--arrow"></span></a>
        </div>
        <div class="">
            <figure>
                <img width="512" height="512" src="/assets/loom73.svg" alt="Loom73 Logo">
                <figcaption>Loom73: Weaving your relationship with technology.</figcaption>
            </figure>
        </div>
    </section>
    <section class="grid grid-2-col breakout">
        <div>
            <h2>Why Loom73?</h2>
            <hr>
            <p>Every application starts by rebuilding many of the same foundations.<br />
            Loom73 starts from a practical question.
            </p>

            <p class="callout">
                “What if the recurring parts were already solved without forcing the rest of the application into a framework?”
            </p>
        </div>
        <div>
            <div class="grid grid-components">
                <div>
                    <div class="stitch stitch--routes x3 color-accent"></div>
                    <span class="label">Routing</span>
                </div>
                <div>
                    <div class="stitch stitch--padlock x3 color-accent"></div>
                    <span class="label">Auth</span>
                </div>
                <div>
                    <div class="stitch stitch--database x3 color-accent"></div>
                    <span class="label">Database</span>
                </div>
                <div>
                    <div class="stitch stitch--image x3 color-accent"></div>
                    <span class="label">Assets</span>
                </div>
                <div>
                    <div class="stitch stitch--cli x3 color-accent"></div>
                    <span class="label">CLI</span>
                </div>
                <div>
                    <div class="stitch stitch--cog x3 color-accent"></div>
                    <span class="label">Sessions</span>
                </div>
                <div>
                    <div class="stitch stitch--layers x3 color-accent"></div>
                    <span class="label">Access Control</span>
                </div>
                <div>
                    <div class="stitch stitch--document x3 color-accent"></div>
                    <span class="label">Logging</span>
                </div>
                <div>
                    <div class="stitch stitch--path x3 color-accent"></div>
                    <span class="label">Audit trail</span>
                </div>
                <div>
                    <div class="stitch stitch--api x3 color-accent"></div>
                    <span class="label">API</span>
                </div>
            </div>
        </div>

    </section>

    <section class="grid grid-2_3-col" id="architecture">
        <div>
            <span class="badge">Architecture</span>
            <h2>A solid structure <br />grants <span class="accent">freedom</span><br/>to build</h2>
            <hr>
            <p>Loom73 is organized around a small number of clearly defined responsibilities.</p>
            <p><strong>Convention, not cage</strong></p>
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
        <div>
            <figure>
                <img width="600" height="797" loading="lazy" src="/assets/raster/medieval-loom.webp" alt="A medieval incision portraying a loom with two people working on the weaving" class="image">
                <figcaption>The Loom, weaving and creating since a very long time ago.</figcaption>
            </figure>
        </div>
    </section>
    <section class="grid grid-25" id="principles">
        <div class="card principle">
            <i class="stitch stitch--path x2 color-accent"></i>

                <h4>Straightforward routing</h4>
                <p>URL maps automatically to controllers and methods. Simple, predictable, readable.</p>

        </div>
        <div class="card principle">

            <i class="stitch stitch--cube x2 color-accent"></i>

                <h4>A small MVC</h4>
                <p>The Controller orchestrates. The Model handles persistence. The View renders. The Template assembles.</p>

        </div>
        <div class="card principle">
            <i class="stitch stitch--database x2 color-accent"></i>

                <h4>SQL when clearer</h4>
                <p>No forced ORM.<br />Explicit SQL is welcome and enforced.</p>

        </div>
        <div class="card principle">
            <i class="stitch stitch--puzzle x2 color-accent"></i>

                <h4>Built to be extended</h4>
                <p>Loom73 provides the foundation.<br />Your application becomes its own thing.</p>

        </div>
    </section>

    <section class="grid bordered">
        <header>
        <h3>Technical Snapshot</h3>
        <hr>
        </header>
        <div class="grid grid-20 with-gap">
            <div class="card ts">
                <span class="ts-label badge">PHP 8.3+</span>
                <p>Built with modern PHP and native extensions.</p>
            </div>
            <div class="card ts">
                <span class="ts-label badge">2 PHP Libraries</span>
                <p>phpdotenv<br />PHPMailer</p>
            </div>
            <div class="card ts">
                <span class="ts-label badge">0 JS Frameworks</span>
                <p>Vanilla JS.<br />Only what you write.</p>
            </div>
            <div class="card ts">
                <span class="ts-label badge">0 CSS Frameworks</span>
                <p>Modern CSS.<br />No runtime CSS libraries.</p>
            </div>
            <div class="card ts">
                <span class="ts-label badge">Minimal runtime</span>
                <p>Small footprint.<br />Maximum control.</p>
            </div>
        </div>
    </section>

    <section class="grid bordered">
        <div class="grid-2-col grid with-gap">
            <figure>
                <img width="1184" height="861" src="/assets/raster/Max_Liebermann-The_Weaver-1882.webp" loading="lazy" class="image" alt="Picture of Max Liebermann's painting 'The Weaver' from 1882">
                <figcaption>Max Liebermann - The Weaver (1882)</figcaption>

            </figure>

            <div class="center-block">
                <h2 class="balance">Start with the decisions<br />that should not need<br />to be made again.</h2>
                <hr />
                <p>Loom73 collects the recurring decisions that should <strong><em>not</em></strong> need<br />to be reconsidered at the beginning of every project.</p>
                <a class="button" href="#">View on Github <span class="stitch stitch--arrow"></span></a> <a class="button hollow" href="#">Read the docs <span class="stitch stitch--arrow"></span></a>
            </div>
        </div>
    </section>
</main>



