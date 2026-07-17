/**
 * Loom73UI
 * Lightweight UI utilities:
 * - Dialog open / close
 * - Dismissible elements
 * - Once-in-view reveal
 *
 * Usage:
 *   import { Loom73UI } from './ui.js';
 *   Loom73UI.init();
 */

export const Loom73UI = {

    init(options = {}) {
        this.options = {
            inViewThreshold: 0.25,
            inViewRootMargin: '0px',
            ...options
        };

        this.initDialogs();
        this.initDismissibles();
        this.initInView();
    },

    /**
     * Dialogs
     *
     * Markup:
     *   <button type="button" data-dialog-open="my-dialog">Open</button>
     *
     *   <dialog id="my-dialog">
     *      ...
     *      <button type="button" data-dialog-close="my-dialog">Close</button>
     *   </dialog>
     */
    initDialogs() {
        document.addEventListener('click', event => {
            const openButton = event.target.closest('[data-dialog-open]');
            const closeButton = event.target.closest('[data-dialog-close]');

            if (openButton) {
                event.preventDefault();

                const dialog = document.getElementById(openButton.dataset.dialogOpen);

                if (!dialog) return;

                if (typeof dialog.showModal === 'function') {
                    dialog.showModal();
                    return;
                }

                dialog.setAttribute('open', '');
                return;
            }

            if (closeButton) {
                event.preventDefault();

                const dialog = document.getElementById(closeButton.dataset.dialogClose);

                if (!dialog) return;

                if (typeof dialog.close === 'function') {
                    dialog.close();
                    return;
                }

                dialog.removeAttribute('open');
            }
        });
    },

    /**
     * Dismissibles
     *
     * Markup:
     *   <div class="flash-message" data-dismissible>
     *      Message
     *      <button type="button" data-dismiss>Close</button>
     *   </div>
     *
     * Or:
     *   <button type="button" data-dismiss=".flash-message">Close</button>
     */
    initDismissibles() {
        document.addEventListener('click', event => {
            const dismissButton = event.target.closest('[data-dismiss]');

            if (!dismissButton) return;

            event.preventDefault();

            const selector = dismissButton.dataset.dismiss;
            let target = null;

            if (selector) {
                target = dismissButton.closest(selector);
            }

            if (!target) {
                target = dismissButton.closest('[data-dismissible]');
            }

            if (!target) {
                target = dismissButton.parentElement;
            }

            if (!target) return;

            target.remove();
        });
    },

    /**
     * Once-in-view
     *
     * Markup:
     *   <section data-inview="is-visible">
     *      ...
     *   </section>
     *
     * Optional:
     *   <section
     *      data-inview="is-visible"
     *      data-inview-threshold="0.4"
     *      data-inview-root-margin="0px 0px -10% 0px">
     *   </section>
     */
    initInView() {
        const elements = document.querySelectorAll('[data-inview]');

        if (!elements.length) return;

        if (!('IntersectionObserver' in window)) {
            elements.forEach(element => {
                this.revealInViewElement(element);
            });

            return;
        }

        const groups = this.groupInViewElementsByOptions(elements);

        groups.forEach(group => {
            const observer = new IntersectionObserver(entries => {
                entries.forEach(entry => {
                    if (!entry.isIntersecting) return;

                    this.revealInViewElement(entry.target);
                    observer.unobserve(entry.target);
                });
            }, group.options);

            group.elements.forEach(element => {
                observer.observe(element);
            });
        });
    },

    revealInViewElement(element) {
        const className = element.dataset.inview;

        if (!className) return;

        className
            .split(' ')
            .filter(Boolean)
            .forEach(name => {
                element.classList.add(name);
            });
    },

    groupInViewElementsByOptions(elements) {
        const groups = new Map();

        elements.forEach(element => {
            const threshold = this.getThreshold(element);
            const rootMargin = this.getRootMargin(element);

            const key = `${threshold}|${rootMargin}`;

            if (!groups.has(key)) {
                groups.set(key, {
                    options: {
                        threshold,
                        rootMargin
                    },
                    elements: []
                });
            }

            groups.get(key).elements.push(element);
        });

        return Array.from(groups.values());
    },

    getThreshold(element) {
        const value = element.dataset.inviewThreshold;

        if (value === undefined) {
            return this.options.inViewThreshold;
        }

        const threshold = Number(value);

        if (Number.isNaN(threshold)) {
            return this.options.inViewThreshold;
        }

        return Math.min(Math.max(threshold, 0), 1);
    },

    getRootMargin(element) {
        return element.dataset.inviewRootMargin || this.options.inViewRootMargin;
    }

};