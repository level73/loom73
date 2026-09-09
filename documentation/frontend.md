# Loom73 Frontend

Loom73 provides a small browser-side foundation built with semantic HTML, layered CSS and Vanilla JavaScript modules.

It does not include a JavaScript framework or a general-purpose CSS framework. The supplied frontend is intended to provide useful defaults and reusable behavior while remaining easy to inspect, replace or adapt in an application fork.

## Frontend references

The frontend documentation is divided by responsibility:

- [Forms](frontend/forms.md) — constraint validation, accessible error feedback and form events.
- [Tables](frontend/tables.md) — client-side search, sorting and pagination.
- [UI utilities](frontend/ui.md) — dialogs, tooltips, dismissible elements, InView and responsive navigation.
- [Stitch icons](frontend/stitch.md) — the CSS-driven SVG icon collection.

The public Components pages provide live examples. These documents describe the markup contracts, configuration and implementation limits.

## Source structure

Frontend source files live under:

```text
frontend-src/
├── css/
├── js/
├── assets/
└── Gruntfile.js
```

Compiled browser assets are written to:

```text
public_html/public/
├── css/main.min.css
├── js/index.min.js
├── js/forms.min.js
├── js/table.min.js
├── js/ui.min.js
└── assets/
```

Application views should load the compiled files rather than files from `frontend-src`.

## JavaScript initialization

The frontend entry point is:

```text
frontend-src/js/index.js
```

It initializes the three JavaScript modules after the document is ready:

```js
Loom73Forms.init();
Loom73Tables.init();
Loom73UI.init();
```

It also adds the following class to the root HTML element:

```text
loom73-ready
```

This class allows CSS enhancements to distinguish a JavaScript-enabled page from the unenhanced document.

The compiled entry point is loaded as a module:

```html
<script type="module" src="/js/index.min.js" defer></script>
```

The default initializer should run once. Forms, tables, dialogs and dismissible elements may register duplicate event listeners if the complete initializer is called repeatedly.

## Progressive enhancement

The frontend utilities preserve useful baseline behavior wherever possible.

### Forms

The `novalidate` attribute is added only when Loom73 form validation initializes.

Without JavaScript, the browser continues to provide its native constraint validation.

### Tables

Without JavaScript, all rows remain visible in their original order. Search, sorting and pagination are enhancements applied to an existing semantic table.

### InView

The initial hidden state is scoped through `.loom73-ready`. Content therefore remains visible when JavaScript does not run.

The supplied CSS also disables the reveal animation when the visitor requests reduced motion.

### Responsive navigation

The navigation remains present in the document without JavaScript. The mobile toggle behavior is applied after frontend initialization.

### Tooltips and dialogs

Tooltip text must not be the only place where essential information is available.

Critical actions should not depend exclusively on a dialog that cannot be opened without JavaScript.

## CSS layers

The layer order is declared explicitly:

```css
@layer reset, layout, components, ui, utilities, specific, stitch;
```

The source files are compiled in the same order:

```text
001-layers.css
    layer order

002-reset.css
    browser normalization and document defaults

003-layout.css
    grids, flex layouts and structural composition

004-components.css
    reusable visual components

005-ui.css
    styles associated with JavaScript UI utilities

006-utilities.css
    small-purpose helpers, feedback and color utilities

007-specific.css
    application and page-specific presentation

008-stitch.css
    Stitch icon system
```

The current baseline includes both structural rules and visual choices. A stronger separation between core behavior, themes and application styles remains planned work rather than part of the current frontend contract.

## Building the frontend

Install the Node development dependencies from the project root:

```console
npm install
```

Compile CSS and JavaScript:

```console
npm run build
```

Optimize and copy image, SVG, favicon and font assets:

```console
npm run build:assets
```

Run the complete deployment build:

```console
npm run build:deploy
```

Build once and watch the frontend source during development:

```console
npm run watch
```

The deployment build:

1. concatenates the CSS layers;
2. minifies the resulting stylesheet;
3. minifies the JavaScript modules;
4. converts supported raster images to WebP;
5. optimizes SVG files while preserving their `viewBox`;
6. copies favicons and fonts without modification.

## Application boundaries

The supplied frontend handles browser interaction. It does not replace application rules.

In particular:

- frontend validation must be repeated by the server;
- tables operate on rows already present in the document;
- dialogs do not authorize the action they contain;
- hiding an element does not change access permissions;
- interface state is not a substitute for persistent application state.

Application forks may remove any frontend utility they do not need or replace it with a project-specific implementation.