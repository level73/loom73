/**
 * Loom73UI
 * Lightweight UI utilities:
 * - Dialog open / close
 * - Dismissible elements
 * - Tooltips
 * - Once-in-view reveal
 * - Responsive navigations (hamburger pattern)
 *
 * Usage:
 *   import { Loom73UI } from './ui.js';
 *   Loom73UI.init();
 */


const initializedNavigations = new WeakSet();
let tooltipElement = null;
let activeTooltipTrigger = null;
let tooltipHideTimer = null;
let tooltipsInitialized = false;

export const Loom73UI = {

    init(options = {}) {
        this.options = {
            inViewThreshold: 0.25,
            inViewRootMargin: '0px',
            navigationBreakpoint: '48rem',
            ...options
        };

        this.initDialogs();
        this.initTooltips();
        this.initDismissibles();
        this.initInView();
        this.initResponsiveNavigation();
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
     * Initializes the Loom73 tooltip behavior.
     *
     * Tooltip triggers are identified by the `[data-tooltip]` attribute.
     * The attribute value is used as plain-text tooltip content.
     *
     * A single tooltip element is created at runtime and shared by all
     * triggers. When a tooltip is active, the trigger is associated with
     * it through `aria-describedby`, preserving any existing descriptions.
     *
     * Tooltips:
     * - open on pointer hover or keyboard focus;
     * - remain visible while the pointer is over the tooltip itself;
     * - close when pointer and focus leave the tooltip context;
     * - close when Escape is pressed;
     * - never receive or move keyboard focus;
     * - contain descriptive text only, not interactive content.
     *
     * Event delegation is used so dynamically added `[data-tooltip]`
     * elements work without requiring reinitialization.
     *
     * This method is idempotent and may safely be called more than once.
     *
     * @returns {void}
     */
    initTooltips() {
        if (tooltipsInitialized) {
            return;
        }

        tooltipElement = document.createElement('div');

        tooltipElement.id = 'loom73-tooltip';
        tooltipElement.className = 'loom73-tooltip';
        tooltipElement.setAttribute('role', 'tooltip');
        tooltipElement.hidden = true;

        document.body.appendChild(tooltipElement);

        const getTrigger = (target) => {
            if (!(target instanceof Element)) {
                return null;
            }

            return target.closest('[data-tooltip]');
        };

        const clearHideTimer = () => {
            if (tooltipHideTimer === null) {
                return;
            }

            window.clearTimeout(tooltipHideTimer);
            tooltipHideTimer = null;
        };

        const addDescription = (trigger) => {
            const describedBy = (
                trigger.getAttribute('aria-describedby') ?? ''
            )
                .split(/\s+/)
                .filter(Boolean);

            if (!describedBy.includes(tooltipElement.id)) {
                describedBy.push(tooltipElement.id);
            }

            trigger.setAttribute(
                'aria-describedby',
                describedBy.join(' ')
            );
        };

        const removeDescription = (trigger) => {
            const describedBy = (
                trigger.getAttribute('aria-describedby') ?? ''
            )
                .split(/\s+/)
                .filter(
                    (id) => id && id !== tooltipElement.id
                );

            if (describedBy.length === 0) {
                trigger.removeAttribute('aria-describedby');
                return;
            }

            trigger.setAttribute(
                'aria-describedby',
                describedBy.join(' ')
            );
        };

        const positionTooltip = (trigger) => {
            if (tooltipElement.hidden) {
                return;
            }

            const triggerRect = trigger.getBoundingClientRect();
            const tooltipRect = tooltipElement.getBoundingClientRect();

            const gap = 8;
            const viewportMargin = 8;

            let top = (
                triggerRect.top
                - tooltipRect.height
                - gap
            );

            let placement = 'top';

            if (top < viewportMargin) {
                top = triggerRect.bottom + gap;
                placement = 'bottom';
            }

            let left = (
                triggerRect.left
                + (triggerRect.width / 2)
                - (tooltipRect.width / 2)
            );

            const maxLeft = (
                window.innerWidth
                - tooltipRect.width
                - viewportMargin
            );

            left = Math.max(
                viewportMargin,
                Math.min(left, maxLeft)
            );

            tooltipElement.style.top = `${top}px`;
            tooltipElement.style.left = `${left}px`;

            tooltipElement.dataset.placement = placement;
        };

        const showTooltip = (trigger) => {
            const content = trigger.dataset.tooltip?.trim();

            if (!content) {
                return;
            }

            clearHideTimer();

            if (
                activeTooltipTrigger
                && activeTooltipTrigger !== trigger
            ) {
                removeDescription(activeTooltipTrigger);
            }

            activeTooltipTrigger = trigger;

            /*
             * textContent is deliberate:
             * data-tooltip is plain descriptive text,
             * never executable or interactive markup.
             */
            tooltipElement.textContent = content;
            tooltipElement.hidden = false;

            addDescription(trigger);
            positionTooltip(trigger);
        };

        const hideTooltip = () => {
            clearHideTimer();

            if (activeTooltipTrigger) {
                removeDescription(activeTooltipTrigger);
            }

            activeTooltipTrigger = null;

            tooltipElement.hidden = true;
            tooltipElement.textContent = '';

            delete tooltipElement.dataset.placement;
        };

        const scheduleHide = () => {
            clearHideTimer();

            tooltipHideTimer = window.setTimeout(() => {
                if (
                    activeTooltipTrigger
                    && activeTooltipTrigger.matches(':hover')
                ) {
                    return;
                }

                if (
                    activeTooltipTrigger
                    && document.activeElement === activeTooltipTrigger
                ) {
                    return;
                }

                if (tooltipElement.matches(':hover')) {
                    return;
                }

                hideTooltip();
            }, 100);
        };

        document.addEventListener(
            'pointerover',
            (event) => {
                const trigger = getTrigger(event.target);

                if (!trigger) {
                    return;
                }

                showTooltip(trigger);
            }
        );

        document.addEventListener(
            'pointerout',
            (event) => {
                const trigger = getTrigger(event.target);

                if (!trigger) {
                    return;
                }

                const relatedTarget = event.relatedTarget;

                if (
                    relatedTarget instanceof Node
                    && trigger.contains(relatedTarget)
                ) {
                    return;
                }

                scheduleHide();
            }
        );

        document.addEventListener(
            'focusin',
            (event) => {
                const trigger = getTrigger(event.target);

                if (!trigger) {
                    return;
                }

                showTooltip(trigger);
            }
        );

        document.addEventListener(
            'focusout',
            (event) => {
                const trigger = getTrigger(event.target);

                if (!trigger) {
                    return;
                }

                scheduleHide();
            }
        );

        document.addEventListener(
            'keydown',
            (event) => {
                if (event.key !== 'Escape') {
                    return;
                }

                if (!activeTooltipTrigger) {
                    return;
                }

                hideTooltip();
            }
        );

        tooltipElement.addEventListener(
            'pointerenter',
            () => {
                clearHideTimer();
            }
        );

        tooltipElement.addEventListener(
            'pointerleave',
            () => {
                scheduleHide();
            }
        );

        window.addEventListener(
            'resize',
            () => {
                if (!activeTooltipTrigger) {
                    return;
                }

                positionTooltip(activeTooltipTrigger);
            }
        );

        window.addEventListener(
            'scroll',
            () => {
                if (!activeTooltipTrigger) {
                    return;
                }

                positionTooltip(activeTooltipTrigger);
            },
            {
                passive: true
            }
        );

        tooltipsInitialized = true;
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

    /**
     * Responsive Navigation
     * Markup:
     * <header data-responsive-nav>
     *   <button data-nav-toggle></button>
     *   <nav data-nav-panel>
     *       <ul>
     *           <li><a></a></li>
     *       </ul>
     *   </nav>
     * </header>
     */

    initResponsiveNavigation(root = document) {
        const navigations = root.querySelectorAll(
            '[data-responsive-nav]'
        );

        navigations.forEach((navigation) => {
            if (initializedNavigations.has(navigation)) {
                return;
            }

            const toggle = navigation.querySelector(
                '[data-nav-toggle]'
            );

            const panel = navigation.querySelector(
                '[data-nav-panel]'
            );

            if (!(toggle instanceof HTMLButtonElement)) {
                return;
            }

            if (!(panel instanceof HTMLElement)) {
                return;
            }

            const media = window.matchMedia(
                `(min-width: ${this.options.navigationBreakpoint})`
            );

            const isOpen = () => {
                return toggle.getAttribute('aria-expanded') === 'true';
            };

            const open = () => {
                toggle.setAttribute('aria-expanded', 'true');
                toggle.querySelector('.stitch').classList.add('stitch--times');
                toggle.querySelector('.stitch').classList.remove('stitch--menu');
                panel.hidden = false;
                navigation.dataset.navOpen = 'true';
            };

            const close = ({ restoreFocus = false } = {}) => {
                toggle.setAttribute('aria-expanded', 'false');
                toggle.querySelector('.stitch').classList.add('stitch--menu');
                toggle.querySelector('.stitch').classList.remove('stitch--times');
                navigation.dataset.navOpen = 'false';

                if (!media.matches) {
                    panel.hidden = true;
                }

                if (restoreFocus) {
                    toggle.focus();
                }
            };

            const synchronize = () => {
                if (media.matches) {
                    toggle.setAttribute('aria-expanded', 'false');
                    navigation.dataset.navOpen = 'false';
                    panel.hidden = false;
                    return;
                }

                close();
            };

            toggle.addEventListener('click', () => {
                if (isOpen()) {
                    close();
                    return;
                }

                open();
            });

            navigation.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape' || !isOpen()) {
                    return;
                }

                close({
                    restoreFocus: true
                });
            });

            panel.addEventListener('click', (event) => {
                const link = event.target.closest('a');

                if (!(link instanceof HTMLAnchorElement)) {
                    return;
                }

                if (!media.matches) {
                    close();
                }
            });

            document.addEventListener('pointerdown', (event) => {
                if (!isOpen()) {
                    return;
                }

                if (
                    event.target instanceof Node
                    && navigation.contains(event.target)
                ) {
                    return;
                }

                close();
            });

            media.addEventListener('change', synchronize);

            synchronize();
            initializedNavigations.add(navigation);
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