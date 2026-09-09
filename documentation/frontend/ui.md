# UI Utilities

`Loom73UI` provides small declarative behaviors through HTML data attributes:

- native dialogs;
- plain-text tooltips;
- dismissible elements;
- once-only InView reveals;
- responsive navigation.

The module is initialized automatically by the default frontend entry point.

## Configuration

Default options are:

```js
{
    inViewThreshold: 0.25,
    inViewRootMargin: '0px',
    navigationBreakpoint: '48rem'
}
```

A fork can change them in `frontend-src/js/index.js`:

```js
Loom73UI.init({
    inViewThreshold: 0.4,
    inViewRootMargin: '0px 0px -10% 0px',
    navigationBreakpoint: '48rem'
});
```

If the navigation breakpoint changes, update the corresponding media query in `frontend-src/css/005-ui.css` as well.

The complete UI initializer should run once. Responsive navigation and tooltips guard against repeated initialization, while dialogs and dismissible elements currently do not.

## Dialogs

Use `data-dialog-open` and `data-dialog-close` with the dialog ID:

```html
<button
    type="button"
    data-dialog-open="contact-dialog"
>
    Open contact information
</button>

<dialog id="contact-dialog">
    <button
        type="button"
        data-dialog-close="contact-dialog"
    >
        <span class="sr-only">Close dialog</span>
        <span
            class="stitch stitch--times"
            aria-hidden="true"
        ></span>
    </button>

    <h2>Contact information</h2>
    <p>Email us at hello@example.com.</p>
</dialog>
```

Opening uses the native `showModal()` method when available. Closing uses the native `close()` method.

The fallback adds or removes the `open` attribute.

The native dialog element provides modal focus handling and Escape behavior in supporting browsers. Application authorization and validation still belong to the action contained inside the dialog.

The utility does not generate dialog content or submit forms.

## Tooltips

Add plain descriptive text with `data-tooltip`:

```html
<button
    type="button"
    data-tooltip="Download the current report"
>
    <span
        class="stitch stitch--download"
        aria-hidden="true"
    ></span>
    Download
</button>
```

Loom73 creates one shared element with:

```html
<div
    id="loom73-tooltip"
    class="loom73-tooltip"
    role="tooltip"
></div>
```

Tooltips:

- open on pointer hover and keyboard focus;
- close after pointer and focus leave;
- close when Escape is pressed;
- remain visible while the pointer is over the tooltip;
- reposition during scrolling and viewport resizing;
- prefer a position above the trigger and fall below it when needed;
- preserve existing `aria-describedby` references;
- use `textContent`, so tooltip values are treated as plain text.

Event delegation allows `[data-tooltip]` triggers added after initialization to work without another call to the initializer.

Tooltip content must not contain links, controls or other interactive markup. Essential instructions should remain available as visible text or through another persistent description.

## Dismissible elements

The usual contract places the dismiss control inside a marked container:

```html
<div
    class="flash flash--success"
    data-dismissible
    role="status"
>
    Your changes were saved.

    <button type="button" class="dismiss" data-dismiss>
        <span class="sr-only">Dismiss message</span>
        <span
            class="stitch stitch--times"
            aria-hidden="true"
        ></span>
    </button>
</div>
```

Activating the button removes the element from the DOM.

A selector can target the nearest matching ancestor:

```html
<section class="notice">
    <p>Scheduled maintenance begins tonight.</p>

    <button type="button" data-dismiss=".notice">
        Dismiss
    </button>
</section>
```

Target resolution follows this order:

1. the nearest ancestor matching the selector in `data-dismiss`;
2. the nearest `[data-dismissible]` ancestor;
3. the button's parent element.

`data-dismiss` should therefore remain inside the element it removes.

Removing a message does not persist dismissal across page loads.

## InView

InView adds one or more classes when an element enters its observation area:

```html
<section data-inview="is-visible">
    <article style="--i: 0">First item</article>
    <article style="--i: 1">Second item</article>
    <article style="--i: 2">Third item</article>
</section>
```

The value of `data-inview` is a space-separated list of classes added to the observed element.

The supplied CSS animates its direct children. The optional `--i` property produces a staggered delay:

```css
animation-delay: calc(var(--i, 0) * 40ms);
```

### Per-element options

```html
<section
    data-inview="is-visible"
    data-inview-threshold="0.4"
    data-inview-root-margin="0px 0px -10% 0px"
>
    <!-- content -->
</section>
```

`data-inview-threshold` accepts a number between `0` and `1`. Values outside that range are clamped. Invalid numeric values fall back to the configured default.

`data-inview-root-margin` accepts Intersection Observer root-margin syntax.

To trigger an element around the middle portion of the viewport, shrink the observation area from the top and bottom:

```html
<div
    data-inview="is-visible"
    data-inview-threshold="0"
    data-inview-root-margin="-35% 0px -35% 0px"
>
    <!-- content -->
</div>
```

Elements with matching threshold and root-margin options share an observer for efficiency, but each element is revealed independently when it intersects.

Each element is unobserved after its first reveal. InView does not remove the class when the element leaves the viewport.

If Intersection Observer is unavailable, all observed elements are revealed immediately.

### JavaScript and reduced motion

Initial hiding is gated by:

```css
.loom73-ready [data-inview] > *
```

Without JavaScript, the content remains visible.

When `prefers-reduced-motion: reduce` is active, the supplied stylesheet displays the content without animation or transform.

## Responsive navigation

The responsive navigation contract requires:

```html
<header data-responsive-nav>
    <a href="/" class="navbar-brand">
        Example
    </a>

    <button
        class="navbar-toggler"
        type="button"
        aria-controls="main-navigation"
        aria-expanded="false"
        data-nav-toggle
    >
        <span
            class="stitch stitch--menu"
            aria-hidden="true"
        ></span>
        <span class="sr-only">Menu</span>
    </button>

    <nav
        class="navbar"
        id="main-navigation"
        aria-label="Main navigation"
        data-nav-panel
    >
        <ul class="navbar-menu">
            <li><a href="/">Home</a></li>
            <li><a href="/about">About</a></li>
        </ul>
    </nav>
</header>
```

The toggle must be a `<button>`, the panel must be an HTML element and the toggle must contain a `.stitch` icon.

On small viewports, the utility:

- toggles the panel's `hidden` state;
- updates `aria-expanded`;
- records state in `data-nav-open`;
- changes the icon between `stitch--menu` and `stitch--times`;
- closes after selecting a link;
- closes after a pointer action outside the navigation;
- closes on Escape and returns focus to the toggle.

At or above the configured breakpoint, the panel remains visible and the toggle state is reset.

Multiple responsive navigations may exist on the same page.
