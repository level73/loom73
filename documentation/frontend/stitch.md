# Stitch Icons

Stitch is Loom73's CSS-driven SVG icon collection.

Each icon is a separate SVG file used as a CSS mask. Its visible color follows the current text color unless explicitly overridden.

Stitch does not use an icon font or an SVG sprite.

## Basic use

An icon requires the base class and an icon class:

```html
<span
    class="stitch stitch--arrow"
    aria-hidden="true"
></span>
```

The base size is `1.25em`, so icons scale with surrounding text.

## Available icons

The default collection contains 38 icons:

```text
api            arrow          bin            caret
check          chevron        cli            cog
cube           danger         database       document
download       error          image          info
layers         leaf           login          logout
mail           menu           minus          outbound
padlock        path           pdf            pencil
plus           puzzle         question       routes
save           search         spreadsheet    times
user           view
```

Use the icon name after the `stitch--` prefix:

```html
<span class="stitch stitch--database" aria-hidden="true"></span>
<span class="stitch stitch--document" aria-hidden="true"></span>
<span class="stitch stitch--search" aria-hidden="true"></span>
```

Each icon remains an individual asset. The default stylesheet contains the complete class collection, while rendered elements reference only the SVG associated with their icon class.

## Scale

Preset scale classes are:

```text
x1.5
x2
x3
x4
x5
x6
```

Because the decimal point is part of the class name, it must remain exactly as shown in HTML:

```html
<span
    class="stitch stitch--search x1.5"
    aria-hidden="true"
></span>

<span
    class="stitch stitch--search x3"
    aria-hidden="true"
></span>
```

## Rotation

Rotation utilities use 45-degree increments:

```text
r45
r90
r135
r180
r225
r270
r315
```

Example:

```html
<span
    class="stitch stitch--arrow r90"
    aria-hidden="true"
></span>
```

## Color

Icons inherit `currentColor`:

```html
<a href="https://example.com">
    Visit example.com
    <span
        class="stitch stitch--outbound"
        aria-hidden="true"
    ></span>
</a>
```

Loom73 color utilities can set the surrounding icon color:

```html
<span class="stitch stitch--check color-success" aria-hidden="true"></span>
<span class="stitch stitch--info color-info" aria-hidden="true"></span>
<span class="stitch stitch--danger color-warning" aria-hidden="true"></span>
<span class="stitch stitch--error color-danger" aria-hidden="true"></span>
```

Available frontend color utilities include:

```text
color-accent
color-accent-hover
color-accent-bright
color-surface
color-border
color-secondary
color-text

color-danger
color-warning
color-info
color-success
```

## Custom values

Stitch exposes four useful CSS custom properties:

| Property | Purpose |
| --- | --- |
| `--stitch-base-size` | Changes the size before scaling. |
| `--stitch-scale` | Multiplies the base size. |
| `--stitch-size` | Sets an explicit width and height. |
| `--stitch-rotation` | Sets an arbitrary rotation. |
| `--stitch-color` | Overrides the inherited color. |

Example:

```html
<span
    class="stitch stitch--leaf"
    style="
        --stitch-color: #8b0cc2;
        --stitch-rotation: -17deg;
        --stitch-scale: 4;
    "
    aria-hidden="true"
></span>
```

An explicit size can be used instead of scale:

```html
<span
    class="stitch stitch--cube"
    style="--stitch-size: 2rem;"
    aria-hidden="true"
></span>
```

## Accessibility

Decorative icons should be hidden from assistive technology:

```html
<button type="button">
    <span
        class="stitch stitch--download"
        aria-hidden="true"
    ></span>
    Download
</button>
```

An icon-only control needs an accessible name on the control:

```html
<button type="button" aria-label="Delete">
    <span
        class="stitch stitch--bin"
        aria-hidden="true"
    ></span>
</button>
```

The icon class describes its drawing, not necessarily the action. The accessible name should describe what the control does.

Do not depend on an icon's color alone to communicate success, warning or failure.

## Adding an icon

To extend Stitch:

1. add the optimized SVG to `frontend-src/assets/icons/`;
2. add its class to `frontend-src/css/008-stitch.css`;
3. run the frontend deployment build.

For an icon named `archive.svg`, the rule is:

```css
.stitch {
    &.stitch--archive::after {
        mask-image: url('../assets/icons/archive.svg');
    }
}
```

Then use it as:

```html
<span
    class="stitch stitch--archive"
    aria-hidden="true"
></span>
```

SVG files should retain a valid `viewBox` so they scale correctly.