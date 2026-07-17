# Loom73 Frontend Features

## Javascript
The full, minified JS that ships to the browser with Loom73 is < 4kb. 

### Form Validation
Loom73 ships natively with frontend form validation. To enable it, you should add the `novalidate` attribute to the form you want to be validated by the script.

### Table Sorting & Searching
Loom73 ships natively with Table Sorting and Searching. 

#### Table Sorting
1. Add the `data-sortable-table` attribute to the table
2. Add the `data-sortable` attribute to the table head cells. This attribute should have one of these three options as a value:
   1. `text` for text entries, such as emails
   2. `number` for numerical sorting
   3. `date` for dates
3. To have Loom73 properly sort based on numeric and date values, leverage the `data-sort-value` attribute in the table body cells, and include the formatted value for numerical data (as in - you have USD currency prepended to the number in the table cell), as well as data in ISO 8601 format for dates (YYYY-MM-DD).  

#### Table Searching
Add a form and an input field with the `data-table-search` attribute, and add the table selector as a value.