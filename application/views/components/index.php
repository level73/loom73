<main id="main-content">
    <section class="grid grid-1_2-col">
        <div>
        <h1>Components</h1>
        <hr />
        <p class="intro">
            The included components that handle the recurring parts in application development.
        </p>
        </div>
        <div>
            <figure>
                <img src="/assets/raster/components.webp" width="1672" height="941" class="image" alt="Ink drawing of heddle, shed and warp mechanism in 19th century looms.">
                <figcaption>The Heddle/Shuttle/Warp mechanism of 19<sup>th</sup> century looms.</figcaption>
            </figure>
        </div>
    </section>

    <section class="grid breakout">
        <div class="grid grid-20 with-gap" data-inview="is-visible" data-inview-threshold="0.2">
            <div class="card component backend" style="--i: 0;">
                <h2>Heddle</h2>
                <p>Manages authentication, roles, permissions, sessions, abilities and guards.</p>
            </div>
            <div class="card component backend" style="--i: 1;">
                <h2>Ledger</h2>
                <p>Meaningful user actions and audit trail.</p>
            </div>
            <div class="card component backend" style="--i: 2;">
                <h2>Yarn</h2>
                <p>Asset management with types, data ownership and visibility.</p>
            </div>
            <div class="card component backend" style="--i: 3;">
                <h2>Logger</h2>
                <p>Operational activity of Loom73 logging.</p>
            </div>
            <div class="card component backend" style="--i: 4;">
                <h2>Weave API</h2>
                <p>Read only JSON API layer, GET only. </p>
            </div>

            <div class="card component frontend" style="--i: 6;">
                <h2>Flash Messages</h2>
                <p>Integrated user feedback system.</p>

            </div>
            <div class="card component frontend" style="--i: 7;">
                <h2>Stitch</h2>
                <p>Lightweight SVG icon system and utilities.</p>
                <a href="/components/stitch-icons">Explore the icon collection <span class="stitch stitch--arrow"></span></a>
            </div>
            <div class="card component frontend" style="--i: 8;">
                <h2>Frontend Utilities</h2>
                <p>Vanilla JS utilities for common UI patterns.</p>
                <a href="#ui-utilities">Explore Loom73 UI utilities <span class="stitch stitch--arrow"></span></a>
            </div>
            <div class="card component cli" style="--i: 10;">
                <h2>Shuttle</h2>
                <p>CLI tools for installation, maintenance and backend operations.</p>
                <a href="/components/shuttle">Learn more about Shuttle <span class="stitch stitch--arrow"></span></a>
            </div>
            <div class="card component frontend coming-soon" style="--i: 12;">
                <h2>Theming</h2>
                <p>Support for custom themes.</p>
                <span class="badge">Coming Soon</span>
            </div>
        </div>
    </section>

    <!-- Frontend Utils -->
    <section class="grid" id="ui-utilities">

        <header>
            <h3>Frontend <span class="accent">Utilities</span></h3>
            <hr>
            <p>Loom73 ships with a handful of useful, vanilla JS frontend utilities. Recurring, always handy, in every project.</p>
            <p>These utilities are all <em>data-attribute driven</em>. It is likely you'll never need to open up the JS file.</p>
            <p>Loom73 utilities are accessible by default. They support keyboard navigation and standard ARIA attributes where necessary and/or relevant.</p>
        </header>

        <div class="utilities-grid">

            <div class="card utility data-tables">
                <h4>Data Tables</h4>
                <p>Tables with live search, sorting (with data-type recognition), paging.</p>
                <a class="button hollow" href="/components/tables">View the Tables Example <i class="stitch stitch--arrow color-accent"></i></a>
            </div>
            <div class="card utility in-view-wrapper">
                <h4>In-view (intersection observer)</h4>
                <p>Manage intersection observers with a data attribute on the HTML element.</p>
                <div data-inview="is-visible" data-inview-threshold="0" data-inview-root-margin="-35% 0px -35% 0px">
                    <figure style="--i: 0;">
                        <img src="/assets/raster/carpets.webp" width="900" height="600" loading="lazy" alt="A zoomed image of carpets, used to show the behaviour of the intersection observer. It shows up on screen only when the DOM element enters the viewport.">
                        <figcaption>
                            Showing up only when the element enters the viewport.<br />
                            <small>Photo by <a href="https://unsplash.com/it/@beyondninefive?utm_source=unsplash&utm_medium=referral&utm_content=creditCopyText">J M</a> on <a href="https://unsplash.com/it/foto/sono-esposti-tappeti-a-motivi-colorati-5TKEAV7rkI4?utm_source=unsplash&utm_medium=referral&utm_content=creditCopyText">Unsplash</a></small>
                        </figcaption>
                    </figure>
                </div>
            </div>
            <div class="card utility forms">
                <h4>Forms</h4>
                <p>Frontend field validation with feedback on :user-invalid and checks the submit event.</p>
                <a href="/components/forms" class="button hollow">View the Forms Example <i class="stitch stitch--arrow color-accent"></i></a>
            </div>
            <div class="card utility dialogs">
                <h4>Dialogs</h4>
                <p>Modern modals. Vanilla JS for the interactivity, everything else is CSS.</p>
                <button class="button hollow" type="button" data-dialog-open="example-dialog">Open the Modal Dialog <i class="stitch stitch--info color-accent"></i></button>
                <dialog id="example-dialog" closedby="any">
                    <button type="button" data-dialog-close="example-dialog"><i class="stitch stitch--times"><span class="sr-only">Close</span></i></button>
                    <h3>Example dialog</h3>
                    <p>Hey there!</p>
                </dialog>
            </div>


            <div class="card utility tooltips">
                <h4>Tooltips</h4>
                <p>These are always handy. One data attribute, accessible, automatic top/bottom placement.</p>
                <button class="button hollow" type="button" data-tooltip="Yes, this is the tooltip in all its glory!">Hover me! <i class="stitch stitch--info color-accent"></i></button>
            </div>

            <div class="card utility dismissables">
                <h4>Flash Messages/Dismissables</h4>
                <p>Dismiss/remove an element from the page.</p>
                <div>
                    <div class="flash flash--success" role="status">
                        <?php flashIcon('success'); ?>
                        Everything went well!
                        <button class="dismiss" type="button" role="button" data-dismiss><span class="sr-only">Dismiss message</span><i class="stitch stitch--times"></i></button>
                    </div>
                    <div class="flash flash--error" role="alert">
                        <?php flashIcon('danger'); ?>
                        Woah, something went wrong here!
                        <button class="dismiss" type="button" role="button" data-dismiss><span class="sr-only">Dismiss message</span><i class="stitch stitch--times"></i></button>
                    </div>
                    <div class="flash flash--warning" role="alert">
                        <?php flashIcon('warning'); ?>
                        Hey, watch out, this is important information.
                        <button class="dismiss" type="button" role="button" data-dismiss><span class="sr-only">Dismiss message</span><i class="stitch stitch--times"></i></button>
                    </div>
                    <div class="flash flash--info"  role="status">
                        <?php flashIcon('info'); ?>
                        An interesting and informative callout, isn't it?
                        <button class="dismiss" type="button" role="button" data-dismiss><span class="sr-only">Dismiss message</span><i class="stitch stitch--times"></i></button>
                    </div>
                </div>

            </div>
            <div class="card utility responsive-navs">
                <h4>Responsive Navs</h4>
                <p>Add responsiveness to any nav element in your application with the well-established <em>make-it-a-hamburger</em> pattern.</p>

            </div>
        </div>
    </section>
    <section class="grid">
        <div>
            <h2>References</h2>
            <hr />
            <a class="button hollow" href="https://github.com/level73/loom73/blob/main/documentation/frontend.md" target="_blank">Read the docs <i class="stitch stitch--arrow"></i></a>
        </div>
    </section>
</main>