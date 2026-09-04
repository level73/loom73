/**
 *  Loom73 JS Bootstrap
 *  import forms, tables, UI
 *  Prepare init all modules
 *  wait for DOMContentLoaded, then run everything.
 */
import { Loom73Forms } from './forms.min.js';
import {Loom73Tables} from "./table.min.js";
import { Loom73UI } from './ui.min.js';

const Loom73 = {
    version: '6.0.0',

    init() {
        Loom73Forms.init();
        Loom73Tables.init();
        Loom73UI.init();
    }
};
/** Boot it up! **/
Loom73.init();


/**document.addEventListener('DOMContentLoaded', () => {
    Loom73.init();
}); */