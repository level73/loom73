/**
 *  Loom73 JS Bootstrap
 *  import forms, tables, UI
 *  Prepare init all modules
 *  wait for DOMContentLoaded, then run everything.
 */
import { Loom73Forms } from './forms.js';
import {Loom73Tables} from "./table.js";
import { Loom73UI } from './ui.js';

const Loom73 = {

    /* This placeholder is replaced by build.mjs using package.json. */
    version: __LOOM73_VERSION__,

    init() {
        Loom73Forms.init();
        Loom73Tables.init();
        Loom73UI.init();
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('loom73-ready');
    Loom73.init();
});