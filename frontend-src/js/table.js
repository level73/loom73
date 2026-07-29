/**
 * Loom73Tables
 * Client-side table utilities:
 * - sorting
 * - live search
 * - pagination
 *
 * Usage:
 *   import { Loom73Tables } from './tables.js';
 *   Loom73Tables.init();
 */

export const Loom73Tables = {
    init() {
        const tables = document.querySelectorAll('[data-table]');

        if (!tables.length) return;

        tables.forEach(table => {
            const controller = new Loom73Table(table);
            controller.init();
        });
    }
};


class Loom73Table {

    constructor(table) {
        this.table = table;
        this.tbody = table.querySelector('tbody');
        this.rows = this.tbody ? Array.from(this.tbody.querySelectorAll('tr')) : [];

        this.state = {
            query: '',
            sortColumn: null,
            sortType: 'text',
            sortDirection: 'ascending',
            page: 1,
            pageSize: this.getPageSize()
        };

        this.searchInput = this.getSearchInput();
        this.pagination = this.getPaginationElement();
    }

    init() {
        if (!this.table || !this.tbody || !this.rows.length) return;

        this.bindSorting();
        this.bindSearch();
        this.bindPagination();

        this.render();
    }

    getPageSize() {
        const pageSize = Number(this.table.dataset.tablePageSize);

        if (Number.isNaN(pageSize) || pageSize < 1) {
            return 15;
        }

        return pageSize;
    }

    getSearchInput() {
        const tableId = this.table.id;
        if (!tableId) return null;
        return document.querySelector(`[data-table-search="#${tableId}"]`);
    }

    getPaginationElement() {
        const tableId = this.table.id;
        if (!tableId) return null;
        return document.querySelector(`[data-table-pagination="#${tableId}"]`);
    }

    bindSorting() {
        const buttons = this.table.querySelectorAll('thead th button[data-table-sort]');

        buttons.forEach(button => {
            const th = button.closest('th');

            if (!th) return;

            if (!th.hasAttribute('aria-sort')) {
                th.setAttribute('aria-sort', 'none');
            }

            button.addEventListener('click', () => {
                const headerRow = th.parentElement;
                const headers = Array.from(headerRow.children);
                const columnIndex = headers.indexOf(th);

                const currentSort = th.getAttribute('aria-sort');
                const direction = currentSort === 'ascending' ? 'descending' : 'ascending';

                headers.forEach(header => {
                    if (header.hasAttribute('aria-sort')) {
                        header.setAttribute('aria-sort', 'none');
                    }
                });

                th.setAttribute('aria-sort', direction);

                this.state.sortColumn = columnIndex;
                this.state.sortType = button.dataset.tableSort || 'text';
                this.state.sortDirection = direction;
                this.state.page = 1;

                this.render();
            });
        });
    }

    bindSearch() {
        if (!this.searchInput) return;

        this.searchInput.addEventListener('input', () => {
            this.state.query = this.normalize(this.searchInput.value);
            this.state.page = 1;

            this.render();
        });
    }

    bindPagination() {
        if (!this.pagination) return;

        this.pagination.addEventListener('click', event => {
            const button = event.target.closest('[data-table-page]');

            if (!button) return;
            event.preventDefault();

            const action = button.dataset.tablePage;
            const totalPages = this.getTotalPages();

            if (action === 'previous') {
                this.state.page = Math.max(1, this.state.page - 1);
            }
            if (action === 'next') {
                this.state.page = Math.min(totalPages, this.state.page + 1);
            }

            this.render();
        });
    }

    render() {
        const matchingRows = this.getMatchingRows();
        const sortedRows = this.getSortedRows(matchingRows);

        this.updateRowOrder(sortedRows);
        this.updateRowVisibility(sortedRows);
        this.renderPagination(sortedRows.length);
    }

    getMatchingRows() {
        if (!this.state.query) {
            return [...this.rows];
        }

        return this.rows.filter(row => {
            const text = this.getSearchableText(row);
            return text.includes(this.state.query);
        });
    }

    getSearchableText(row) {
        const cells = Array.from(row.children).filter(cell => {
            return !cell.hasAttribute('data-table-ignore-search');
        });

        return this.normalize(
            cells
                .map(cell => cell.textContent)
                .join(' ')
        );
    }

    getSortedRows(rows) {
        if (this.state.sortColumn === null) {
            return [...rows];
        }

        return [...rows].sort((rowA, rowB) => {
            const cellA = rowA.children[this.state.sortColumn];
            const cellB = rowB.children[this.state.sortColumn];

            const valueA = this.getSortableValue(cellA);
            const valueB = this.getSortableValue(cellB);

            const result = this.compareValues(
                valueA,
                valueB,
                this.state.sortType
            );

            return this.state.sortDirection === 'ascending'
                ? result
                : -result;
        });
    }

    updateRowOrder(rows) {
        rows.forEach(row => {
            this.tbody.appendChild(row);
        });
    }

    updateRowVisibility(rows) {
        const start = (this.state.page - 1) * this.state.pageSize;
        const end = start + this.state.pageSize;

        const visibleRows = new Set(rows.slice(start, end));

        this.rows.forEach(row => {
            row.hidden = !visibleRows.has(row);
        });
    }

    renderPagination(totalMatchingRows) {
        if (!this.pagination) return;

        const totalPages = Math.ceil(totalMatchingRows / this.state.pageSize);

        if (totalPages <= 1) {
            this.pagination.hidden = true;
            this.pagination.innerHTML = '';
            return;
        }

        this.pagination.hidden = false;
        if (this.state.page > totalPages) {
            this.state.page = totalPages;
        }

        const previousDisabled = this.state.page <= 1 ? 'disabled' : '';
        const nextDisabled = this.state.page >= totalPages ? 'disabled' : '';

        this.pagination.innerHTML = `
            <div class="table-pagination">
                <button
                    type="button"
                    data-table-page="previous"
                    ${previousDisabled}>
                    Previous
                </button>
                <span aria-live="polite">
                    Page ${this.state.page} of ${totalPages}
                </span>
                <button
                    type="button"
                    data-table-page="next"
                    ${nextDisabled}>
                    Next
                </button>
            </div>
        `;
    }

    getTotalPages() {
        const totalMatchingRows = this.getMatchingRows().length;
        return Math.max(
            1,
            Math.ceil(totalMatchingRows / this.state.pageSize)
        );
    }

    getSortableValue(cell) {
        if (!cell) return '';
        if (cell.dataset.sortValue !== undefined) {
            return cell.dataset.sortValue.trim();
        }
        return cell.textContent.trim();
    }

    compareValues(valueA, valueB, type) {
        if (type === 'number') {
            return this.toNumber(valueA) - this.toNumber(valueB);
        }
        if (type === 'date') {
            return this.toDate(valueA) - this.toDate(valueB);
        }
        return valueA.localeCompare(valueB, document.documentElement.lang || 'en', {
            sensitivity: 'base',
            numeric: true
        });
    }

    toNumber(value) {
        const normalized = value
            .replace(/\./g, '')
            .replace(',', '.');

        const number = parseFloat(normalized);
        return Number.isNaN(number) ? 0 : number;
    }

    toDate(value) {
        const timestamp = new Date(value).getTime();

        return Number.isNaN(timestamp) ? 0 : timestamp;
    }

    normalize(value) {
        return value
            .toLowerCase()
            .normalize('NFD')
            .replace(/\p{Diacritic}/gu, '')
            .trim();
    }

}