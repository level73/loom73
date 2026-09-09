# Tables

Loom73 Tables adds client-side search, column sorting and pagination to semantic HTML tables.

The module operates on rows already rendered in the document. It does not request data from the server.

## Smallest working example

```html
<form role="search" class="table-search">
    <label for="people-search" class="sr-only">
        Search people
    </label>

    <input
        type="search"
        id="people-search"
        data-table-search="#people-table"
        placeholder="Search people"
    >

    <span
        class="stitch stitch--search"
        aria-hidden="true"
    ></span>
</form>

<div
    class="table-scroll"
    role="region"
    aria-label="People"
    tabindex="0"
>
    <table
        id="people-table"
        data-table
        data-table-page-size="2"
    >
        <thead>
            <tr>
                <th>
                    <button type="button" data-table-sort="number">
                        ID
                    </button>
                </th>
                <th>
                    <button type="button" data-table-sort="text">
                        Name
                    </button>
                </th>
                <th>
                    <button type="button" data-table-sort="date">
                        Registered
                    </button>
                </th>
                <th>Profile</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td data-sort-value="1">1</td>
                <td>Ada Rossi</td>
                <td data-sort-value="2024-02-12">12 February 2024</td>
                <td data-table-ignore-search>
                    <a href="/people/1">View</a>
                </td>
            </tr>

            <tr>
                <td data-sort-value="2">2</td>
                <td>Sam Lee</td>
                <td data-sort-value="2023-11-08">8 November 2023</td>
                <td data-table-ignore-search>
                    <a href="/people/2">View</a>
                </td>
            </tr>

            <tr>
                <td data-sort-value="3">3</td>
                <td>Nora García</td>
                <td data-sort-value="2025-01-20">20 January 2025</td>
                <td data-table-ignore-search>
                    <a href="/people/3">View</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<nav
    data-table-pagination="#people-table"
    aria-label="People table pagination"
></nav>
```

`Loom73Tables.init()` is called automatically by the default frontend entry point.

## Table attributes

| Attribute | Purpose |
| --- | --- |
| `data-table` | Activates the table controller. |
| `data-table-page-size="10"` | Sets the number of visible rows. The default is `15`. |
| `data-table-search="#people-table"` | Associates a search input with a table ID. |
| `data-table-pagination="#people-table"` | Selects the element that will contain pagination. |
| `data-table-sort="text"` | Sorts a column as text. |
| `data-table-sort="number"` | Sorts a column using the numeric parser. |
| `data-table-sort="date"` | Sorts a column as a JavaScript date. |
| `data-sort-value="..."` | Supplies a sortable value independent of displayed text. |
| `data-table-ignore-search` | Excludes a cell from the searchable row text. |

A table only needs an `id` when it is associated with search or pagination controls.

## Sorting

Sortable headings must contain a button:

```html
<th>
    <button type="button" data-table-sort="text">
        Surname
    </button>
</th>
```

Loom73 sets `aria-sort` on sortable heading cells and updates it between:

```text
none
ascending
descending
```

Selecting a new column resets any previous sortable heading to `none`.

### Displayed and sortable values

Use `data-sort-value` when the displayed value is formatted for readers:

```html
<td data-sort-value="2025-04-17">
    17 April 2025
</td>
```

Dates should use an unambiguous ISO value:

```text
YYYY-MM-DD
```

The current numeric parser expects formatting where periods separate thousands and a comma separates decimals:

```html
<td data-sort-value="1234,50">
    €1.234,50
</td>
```

Integers can be supplied directly:

```html
<td data-sort-value="24">24 papers</td>
```

Applications using another numeric convention should adapt `toNumber()` in `frontend-src/js/table.js`.

Text sorting uses the language declared on the root HTML element and compares values without case or accent sensitivity.

## Search

Search is case-insensitive and accent-insensitive.

A search for:

```text
garcia
```

can therefore match:

```text
García
```

The searchable value is built from the text of every row cell except cells marked with:

```html
<td data-table-ignore-search>
```

Searching resets pagination to the first page.

## Pagination

Pagination is generated inside the selected container:

```html
<nav
    data-table-pagination="#people-table"
    aria-label="People table pagination"
></nav>
```

It contains:

- a previous-page button;
- the current page and total page count;
- a next-page button;
- Stitch chevron icons.

The page count reflects the current search results. Pagination is hidden when the result fits on one page.

The module does not currently provide an empty-results message. When no rows match, all rows and the pagination controls are hidden. An application that needs explicit empty-state feedback should add that behavior separately.

## Responsive tables

The JavaScript module does not alter table layout.

For wide tables, place the table inside a labelled, keyboard-focusable scrolling region:

```html
<div
    class="table-scroll"
    role="region"
    aria-label="Orders"
    tabindex="0"
>
    <table data-table>
        <!-- table content -->
    </table>
</div>
```

## Runtime limits

A table controller captures its rows during initialization.

Rows inserted later are not automatically added to its internal collection. Initialize tables only after their initial markup is complete, and avoid calling the complete table initializer repeatedly on the same table.

This utility is intended for bounded datasets already present in the page. Large datasets, server-side filtering and database-backed pagination belong in application controllers and queries.

Without JavaScript, the original semantic table remains readable with every row visible.