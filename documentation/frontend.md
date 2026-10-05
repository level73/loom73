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
├── assets/
├── css/
│   ├── index.css
│   ├── core/
│   ├── themes/
│   │   ├── plain.css
│   │   └── plain/
│   └── application/
│       └── site.css
├── js/
│   ├── index.js
│   ├── forms.js
│   ├── table.js
│   └── ui.js
└── build.mjs
```

Compiled browser assets are written to:

```text
public_html/public/
├── css/
│   ├── main.min.css
│   └── theme-plain.min.css
├── js/
│   └── index.min.js
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

## CSS organization

The layer order is declared explicitly:

```css
@layer reset, theme, layout, components, ui, stitch, utilities, application;
```

The main CSS entry point is:

```text
frontend-src/css/index.css
```
It imports:

```text
core/layers.css
core/reset.css
core/layout.css
core/components.css
core/ui.css
core/stitch.css
core/utilities.css
application/site.css
```

The core files provide reusable structure, behavior and utility contracts. Application-specific presentation belongs in application/site.css.
Themes use separate entry files under:

```text
frontend-src/css/themes
```

The supplied Plain theme is organized as:

```text
themes/plain.css
themes/plain/foundation.css
themes/plain/components.css
themes/plain/ui.css
```

Every CSS file placed directly under `themes/` is treated as a theme entry point. For example:

```text
themes/plain.css
```

is compiled to:

```text
public_html/public/css/theme-plain.min.css
```

## Theme selection

The active theme is configured in `config/example.env`:

```env
LOOM73_THEME='plain'
```

The shared head template loads `main.min.css` followed by the selected theme stylesheet.

Theme identifiers may contain lowercase letters, numbers and hyphens. An invalid identifier or a theme without a compiled stylesheet falls back to `theme-plain.min.css`.

This convention provides replaceable visual themes without introducing theme inheritance, template overrides or database-managed theme selection.

## Building the frontend

Install the exact Node development dependencies from the project root:

```console
npm ci
```

Run the complete frontend build once:

```console
npm run build
```

Optimize and copy image, SVG, favicon, fonts and other static assets:

```console
npm run build:assets
```

Run the complete build used during deployment:

```console
npm run build:deploy
```

Build the frontend in development mode and watch the sources:

```console
npm run watch
```

The build runner:

1. bundles and minifies the main CSS entry point;
2. discovers, bundles and minifies every theme entry point;
3. bundles and minifies the JavaScript module graph into `index.min.js`;
4. injects the package version into the JavaScript build;
5. converts supported raster images to WebP;
6. optimizes SVG files while preserving their `viewBox`;
7. copies fonts, favicons and static assets;
8. reports source-aware errors, output savings and task duration.

Browser targets are declared once in `frontend-src/build.mjs` and shared by Lightning CSS and esbuild.

Development watch mode generates linked CSS and JavaScript source maps.

Production CSS and JavaScript builds do not generate source maps and remove stale map files left by a previous watch session.

Do not run `watch` and a production `build` at the same time because both processes write to the same public asset files.

## Application boundaries

The supplied frontend handles browser interaction. It does not replace application rules.

In particular:

- frontend validation must be repeated by the server;
- tables operate on rows already present in the document;
- dialogs do not authorize the action they contain;
- hiding an element does not change access permissions;
- interface state is not a substitute for persistent application state.

Application forks may remove any frontend utility they do not need or replace it with a project-specific implementation.