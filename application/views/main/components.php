






<section>
    <span class="badge">Icons</span>
    <h2>Loom73 <span class="accent">Stitch</span></h2>
    <p>A small collection of handcrafted SVG icons, optimized for modern web applications. No fonts. No sprites. Just the icons we actually use.</p>
</section>
<section class="grid grid-6_5" data-inview="is-visible">
    <div class="card icon-container" style="--i: 0"><span class="icon color-accent x2 arrow"></span><span class="icon-name">Arrow</span></div>
    <div class="card icon-container" style="--i: 1"><span class="icon color-accent x2 bin"></span><span class="icon-name">Bin</span></div>
    <div class="card icon-container" style="--i: 2"><span class="icon color-accent x2 caret"></span><span class="icon-name">Caret</span></div>
    <div class="card icon-container" style="--i: 3"><span class="icon color-accent x2 check"></span><span class="icon-name">Check</span></div>
    <div class="card icon-container" style="--i: 4"><span class="icon color-accent x2 chevron"></span><span class="icon-name">Chevron</span></div>
    <div class="card icon-container" style="--i: 5"><span class="icon color-accent x2 cog"></span><span class="icon-name">Cog</span></div>
    <div class="card icon-container" style="--i: 6"><span class="icon color-accent x2 cube"></span><span class="icon-name">Cube</span></div>
    <div class="card icon-container" style="--i: 7"><span class="icon color-accent x2 danger"></span><span class="icon-name">Danger</span></div>
    <div class="card icon-container" style="--i: 8"><span class="icon color-accent x2 database"></span><span class="icon-name">Database</span></div>
    <div class="card icon-container" style="--i: 9"><span class="icon color-accent x2 document"></span><span class="icon-name">Document</span></div>
    <div class="card icon-container" style="--i: 10"><span class="icon color-accent x2 download"></span><span class="icon-name">Download</span></div>
    <div class="card icon-container" style="--i: 11"><span class="icon color-accent x2 error"></span><span class="icon-name">Error</span></div>
    <div class="card icon-container" style="--i: 12"><span class="icon color-accent x2 info"></span><span class="icon-name">Info</span></div>
    <div class="card icon-container" style="--i: 13"><span class="icon color-accent x2 layers"></span><span class="icon-name">Layers</span></div>
    <div class="card icon-container" style="--i: 14"><span class="icon color-accent x2 leaf"></span><span class="icon-name">Leaf</span></div>
    <div class="card icon-container" style="--i: 15"><span class="icon color-accent x2 login"></span><span class="icon-name">Login</span></div>
    <div class="card icon-container" style="--i: 16"><span class="icon color-accent x2 logout"></span><span class="icon-name">Logout</span></div>
    <div class="card icon-container" style="--i: 17"><span class="icon color-accent x2 mail"></span><span class="icon-name">Mail</span></div>
    <div class="card icon-container" style="--i: 18"><span class="icon color-accent x2 menu"></span><span class="icon-name">Menu</span></div>
    <div class="card icon-container" style="--i: 19"><span class="icon color-accent x2 minus"></span><span class="icon-name">Minus</span></div>
    <div class="card icon-container" style="--i: 20"><span class="icon color-accent x2 outbound"></span><span class="icon-name">Outbound</span></div>
    <div class="card icon-container" style="--i: 21"><span class="icon color-accent x2 pdf"></span><span class="icon-name">PDF</span></div>
    <div class="card icon-container" style="--i: 22"><span class="icon color-accent x2 pencil"></span><span class="icon-name">Pencil</span></div>
    <div class="card icon-container" style="--i: 23"><span class="icon color-accent x2 plus"></span><span class="icon-name">Plus</span></div>
    <div class="card icon-container" style="--i: 24"><span class="icon color-accent x2 puzzle"></span><span class="icon-name">Puzzle</span></div>
    <div class="card icon-container" style="--i: 25"><span class="icon color-accent x2 question"></span><span class="icon-name">Question</span></div>
    <div class="card icon-container" style="--i: 26"><span class="icon color-accent x2 save"></span><span class="icon-name">Save</span></div>
    <div class="card icon-container" style="--i: 27"><span class="icon color-accent x2 search"></span><span class="icon-name">Search</span></div>
    <div class="card icon-container" style="--i: 28"><span class="icon color-accent x2 spreadsheet"></span><span class="icon-name">Spreadsheet</span></div>
    <div class="card icon-container" style="--i: 29"><span class="icon color-accent x2 times"></span><span class="icon-name">Times</span></div>
    <div class="card icon-container" style="--i: 30"><span class="icon color-accent x2 user"></span><span class="icon-name">User</span></div>
    <div class="card icon-container" style="--i: 31"><span class="icon color-accent x2 image"></span><span class="icon-name">Image</span></div>
</section>
<section>
    <span class="badge">CLI</span>
    <h3>Loom73 <span class="accent">Shuttle</span></h3>
    <p>An easy to use, friendly and extensible CLI.</p>
    <div class="coding-window">
        <span class="shuttle-username">You@TheServer</span> <span class="shuttle-path">/var/www/Loom73</span><br />
        <span class="shuttle-inline-symbol">$</span> <span class="input-line">php shuttle loom73.info</span><br />
        <span class="output-lines">Shuttle version: 0.1</span><br />
        <span class="output-lines">Loom73 version: 6.0</span><br />
    </div>
</section>

<section>
    <span class="badge">UI</span>
    <h3>A UI that you can brand in <span class="accent">seconds</span></h3>
    <p>All UI components of Loom73 are coded in modern CSS. Tweak a few custom properties and see it change in seconds.</p>

    <div class="grid">
        <div class="flash flash--success">
            <?php flashIcon('success'); ?>
            Everything went well!
            <button class="dismiss" role="button" aria-label="Dismiss this notification"><i class="icon times"></i></button>
        </div>
        <div class="flash flash--danger">
            <?php flashIcon('danger'); ?>
            Woah, something went wrong here!
            <button class="dismiss" role="button" aria-label="Dismiss this notification"><i class="icon times"></i></button>
        </div>
        <div class="flash flash--warning">
            <?php flashIcon('warning'); ?>
            Hey, watch out, this is important information.
            <button class="dismiss" role="button" aria-label="Dismiss this notification"><i class="icon times"></i></button>
        </div>
        <div class="flash flash--info">
            <?php flashIcon('info'); ?>
            An interesting and informative callout, isn't it?
            <button class="dismiss" role="button" aria-label="Dismiss this notification"><i class="icon times"></i></button>
        </div>
    </div>


    <form role="search" class="table-search">
        <label for="organizations-search" class="sr-only">Search organizations</label>

        <input
            type="search"
            id="organizations-search"
            data-table-search="#organizations-table"
            placeholder="Search organizations">

        <span class="icon search" aria-hidden="true"></span>
    </form>

    <table
        id="organizations-table"
        data-table
        data-table-page-size="10">
        <caption>Organizations</caption>

        <thead>
        <tr>
            <th aria-sort="none">
                <button type="button" data-table-sort="number">ID</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">Organization</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">Type</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">City</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">Country</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">Contact</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">Email</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="date">Joined</button>
            </th>

            <th aria-sort="none">
                <button type="button" data-table-sort="text">Status</button>
            </th>

            <th>
                <span class="sr-only">Actions</span>
            </th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td data-sort-value="1001">1001</td>
            <td>Northbridge Civic Lab</td>
            <td>Research Center</td>
            <td>Manchester</td>
            <td>United Kingdom</td>
            <td>Amelia Grant</td>
            <td>amelia.grant@northbridge.example</td>
            <td data-sort-value="2024-01-15">15 Jan 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1002">1002</td>
            <td>Fondazione Aurora Sociale</td>
            <td>Foundation</td>
            <td>Rome</td>
            <td>Italy</td>
            <td>Giulia Moretti</td>
            <td>giulia.moretti@aurorasociale.example</td>
            <td data-sort-value="2023-09-22">22 Sep 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1003">1003</td>
            <td>Open Fields Initiative</td>
            <td>NGO</td>
            <td>Dublin</td>
            <td>Ireland</td>
            <td>Connor Walsh</td>
            <td>connor.walsh@openfields.example</td>
            <td data-sort-value="2022-11-04">4 Nov 2022</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1004">1004</td>
            <td>Centro Horizonte</td>
            <td>Community Group</td>
            <td>Madrid</td>
            <td>Spain</td>
            <td>Lucía Navarro</td>
            <td>lucia.navarro@horizonte.example</td>
            <td data-sort-value="2025-02-10">10 Feb 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1005">1005</td>
            <td>Green Harbor Network</td>
            <td>Environmental NGO</td>
            <td>Rotterdam</td>
            <td>Netherlands</td>
            <td>Sanne de Vries</td>
            <td>sanne.devries@greenharbor.example</td>
            <td data-sort-value="2024-06-18">18 Jun 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1006">1006</td>
            <td>Maison des Liens</td>
            <td>Association</td>
            <td>Lyon</td>
            <td>France</td>
            <td>Claire Bernard</td>
            <td>claire.bernard@maisondesliens.example</td>
            <td data-sort-value="2021-12-01">1 Dec 2021</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1007">1007</td>
            <td>Data Commons Studio</td>
            <td>Technology Lab</td>
            <td>Berlin</td>
            <td>Germany</td>
            <td>Jonas Keller</td>
            <td>jonas.keller@datacommons.example</td>
            <td data-sort-value="2023-03-14">14 Mar 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1008">1008</td>
            <td>Blue Canopy Trust</td>
            <td>Charity</td>
            <td>Edinburgh</td>
            <td>United Kingdom</td>
            <td>Fiona MacLeod</td>
            <td>fiona.macleod@bluecanopy.example</td>
            <td data-sort-value="2020-07-29">29 Jul 2020</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1009">1009</td>
            <td>Bridgeway Health Collective</td>
            <td>Health Organization</td>
            <td>Lisbon</td>
            <td>Portugal</td>
            <td>Mariana Costa</td>
            <td>mariana.costa@bridgewayhealth.example</td>
            <td data-sort-value="2024-04-09">9 Apr 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1010">1010</td>
            <td>Civic Patterns Institute</td>
            <td>Policy Institute</td>
            <td>Brussels</td>
            <td>Belgium</td>
            <td>Thomas Peeters</td>
            <td>thomas.peeters@civicpatterns.example</td>
            <td data-sort-value="2025-01-27">27 Jan 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1011">1011</td>
            <td>Riverstone Youth Forum</td>
            <td>Youth Organization</td>
            <td>Prague</td>
            <td>Czechia</td>
            <td>Eva Novak</td>
            <td>eva.novak@riverstoneyouth.example</td>
            <td data-sort-value="2023-08-11">11 Aug 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1012">1012</td>
            <td>Common Root Foundation</td>
            <td>Foundation</td>
            <td>Vienna</td>
            <td>Austria</td>
            <td>Matthias Gruber</td>
            <td>matthias.gruber@commonroot.example</td>
            <td data-sort-value="2022-05-06">6 May 2022</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1013">1013</td>
            <td>Urban Signals Lab</td>
            <td>Research Lab</td>
            <td>Copenhagen</td>
            <td>Denmark</td>
            <td>Freja Nielsen</td>
            <td>freja.nielsen@urbansignals.example</td>
            <td data-sort-value="2024-10-03">3 Oct 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1014">1014</td>
            <td>South Gate Community Works</td>
            <td>Community Group</td>
            <td>Valletta</td>
            <td>Malta</td>
            <td>Elena Borg</td>
            <td>elena.borg@southgateworks.example</td>
            <td data-sort-value="2021-04-19">19 Apr 2021</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1015">1015</td>
            <td>Open Shelter Alliance</td>
            <td>NGO</td>
            <td>Warsaw</td>
            <td>Poland</td>
            <td>Anna Kowalska</td>
            <td>anna.kowalska@openshelter.example</td>
            <td data-sort-value="2023-02-25">25 Feb 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1016">1016</td>
            <td>Nordic Access Project</td>
            <td>Accessibility Group</td>
            <td>Oslo</td>
            <td>Norway</td>
            <td>Ingrid Solberg</td>
            <td>ingrid.solberg@nordicaccess.example</td>
            <td data-sort-value="2025-03-12">12 Mar 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1017">1017</td>
            <td>Signal House Europe</td>
            <td>Media Organization</td>
            <td>Helsinki</td>
            <td>Finland</td>
            <td>Aino Korhonen</td>
            <td>aino.korhonen@signalhouse.example</td>
            <td data-sort-value="2022-09-17">17 Sep 2022</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1018">1018</td>
            <td>Public Interest Systems</td>
            <td>Technology Nonprofit</td>
            <td>Tallinn</td>
            <td>Estonia</td>
            <td>Karl Tamm</td>
            <td>karl.tamm@publicsystems.example</td>
            <td data-sort-value="2024-12-05">5 Dec 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1019">1019</td>
            <td>Harborline Social Research</td>
            <td>Research Center</td>
            <td>Stockholm</td>
            <td>Sweden</td>
            <td>Oskar Lind</td>
            <td>oskar.lind@harborline.example</td>
            <td data-sort-value="2020-11-30">30 Nov 2020</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1020">1020</td>
            <td>New Commons Network</td>
            <td>Network</td>
            <td>Athens</td>
            <td>Greece</td>
            <td>Eleni Papadakis</td>
            <td>eleni.papadakis@newcommons.example</td>
            <td data-sort-value="2023-06-21">21 Jun 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1021">1021</td>
            <td>Terra Nova Education</td>
            <td>Education NGO</td>
            <td>Turin</td>
            <td>Italy</td>
            <td>Paolo Ricci</td>
            <td>paolo.ricci@terranovaedu.example</td>
            <td data-sort-value="2024-02-28">28 Feb 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1022">1022</td>
            <td>Eastline Policy Forum</td>
            <td>Policy Institute</td>
            <td>Budapest</td>
            <td>Hungary</td>
            <td>Dávid Farkas</td>
            <td>david.farkas@eastlinepolicy.example</td>
            <td data-sort-value="2025-04-01">1 Apr 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1023">1023</td>
            <td>Clearwater Climate Desk</td>
            <td>Environmental NGO</td>
            <td>Zurich</td>
            <td>Switzerland</td>
            <td>Lea Meier</td>
            <td>lea.meier@clearwaterclimate.example</td>
            <td data-sort-value="2023-10-16">16 Oct 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1024">1024</td>
            <td>Neighbourhood Atlas</td>
            <td>Civic Tech Group</td>
            <td>Barcelona</td>
            <td>Spain</td>
            <td>Marta Soler</td>
            <td>marta.soler@neighbourhoodatlas.example</td>
            <td data-sort-value="2022-01-13">13 Jan 2022</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1025">1025</td>
            <td>Stonepath Human Rights</td>
            <td>Human Rights NGO</td>
            <td>Geneva</td>
            <td>Switzerland</td>
            <td>Marc Dubois</td>
            <td>marc.dubois@stonepathrights.example</td>
            <td data-sort-value="2021-08-07">7 Aug 2021</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1026">1026</td>
            <td>Digital Stewardship Bureau</td>
            <td>Technology Lab</td>
            <td>Ghent</td>
            <td>Belgium</td>
            <td>Els Vermeulen</td>
            <td>els.vermeulen@digitalstewardship.example</td>
            <td data-sort-value="2024-08-23">23 Aug 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1027">1027</td>
            <td>Bright Room Collective</td>
            <td>Arts Organization</td>
            <td>Florence</td>
            <td>Italy</td>
            <td>Elisa Conti</td>
            <td>elisa.conti@brightroom.example</td>
            <td data-sort-value="2020-05-12">12 May 2020</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1028">1028</td>
            <td>Shared Futures Agency</td>
            <td>Social Enterprise</td>
            <td>Paris</td>
            <td>France</td>
            <td>Julien Martin</td>
            <td>julien.martin@sharedfutures.example</td>
            <td data-sort-value="2025-05-19">19 May 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1029">1029</td>
            <td>Lakeview Public Health</td>
            <td>Health Organization</td>
            <td>Ljubljana</td>
            <td>Slovenia</td>
            <td>Nina Horvat</td>
            <td>nina.horvat@lakeviewhealth.example</td>
            <td data-sort-value="2023-12-12">12 Dec 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1030">1030</td>
            <td>Frontier Learning Hub</td>
            <td>Education NGO</td>
            <td>Zagreb</td>
            <td>Croatia</td>
            <td>Ivan Kovač</td>
            <td>ivan.kovac@frontierlearning.example</td>
            <td data-sort-value="2022-03-26">26 Mar 2022</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1031">1031</td>
            <td>Civic Orchard</td>
            <td>Community Group</td>
            <td>Riga</td>
            <td>Latvia</td>
            <td>Laura Ozola</td>
            <td>laura.ozola@civicorchard.example</td>
            <td data-sort-value="2024-07-02">2 Jul 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1032">1032</td>
            <td>Open Harbor Foundation</td>
            <td>Foundation</td>
            <td>Hamburg</td>
            <td>Germany</td>
            <td>Klara Hoffmann</td>
            <td>klara.hoffmann@openharbor.example</td>
            <td data-sort-value="2021-10-08">8 Oct 2021</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1033">1033</td>
            <td>Social Metrics Lab</td>
            <td>Research Lab</td>
            <td>Amsterdam</td>
            <td>Netherlands</td>
            <td>Noah Jansen</td>
            <td>noah.jansen@socialmetrics.example</td>
            <td data-sort-value="2023-05-15">15 May 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1034">1034</td>
            <td>Open Hands Relief</td>
            <td>Humanitarian NGO</td>
            <td>Nicosia</td>
            <td>Cyprus</td>
            <td>Andreas Georgiou</td>
            <td>andreas.georgiou@openhands.example</td>
            <td data-sort-value="2024-11-17">17 Nov 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1035">1035</td>
            <td>Citizen Matter Studio</td>
            <td>Civic Tech Group</td>
            <td>Vilnius</td>
            <td>Lithuania</td>
            <td>Monika Petrauskas</td>
            <td>monika.petrauskas@citizenmatter.example</td>
            <td data-sort-value="2025-06-02">2 Jun 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1036">1036</td>
            <td>Silverline Inclusion Network</td>
            <td>Accessibility Group</td>
            <td>Luxembourg</td>
            <td>Luxembourg</td>
            <td>Camille Weber</td>
            <td>camille.weber@silverline.example</td>
            <td data-sort-value="2020-09-09">9 Sep 2020</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1037">1037</td>
            <td>Rural Knowledge Exchange</td>
            <td>Network</td>
            <td>Galway</td>
            <td>Ireland</td>
            <td>Siobhán Murphy</td>
            <td>siobhan.murphy@ruralknowledge.example</td>
            <td data-sort-value="2022-12-20">20 Dec 2022</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1038">1038</td>
            <td>Open Archive Society</td>
            <td>Archives</td>
            <td>Oxford</td>
            <td>United Kingdom</td>
            <td>Henry Clarke</td>
            <td>henry.clarke@openarchive.example</td>
            <td data-sort-value="2023-07-31">31 Jul 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1039">1039</td>
            <td>Good City Observatory</td>
            <td>Urban Observatory</td>
            <td>Milan</td>
            <td>Italy</td>
            <td>Federico Galli</td>
            <td>federico.galli@goodcity.example</td>
            <td data-sort-value="2021-06-03">3 Jun 2021</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1040">1040</td>
            <td>Careline Community Trust</td>
            <td>Charity</td>
            <td>Bristol</td>
            <td>United Kingdom</td>
            <td>Rachel Evans</td>
            <td>rachel.evans@careline.example</td>
            <td data-sort-value="2024-03-07">7 Mar 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1041">1041</td>
            <td>Learning Commons Europe</td>
            <td>Education Network</td>
            <td>Bratislava</td>
            <td>Slovakia</td>
            <td>Petra Kováčová</td>
            <td>petra.kovacova@learningcommons.example</td>
            <td data-sort-value="2025-01-09">9 Jan 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1042">1042</td>
            <td>Field Notes Foundation</td>
            <td>Foundation</td>
            <td>Reykjavík</td>
            <td>Iceland</td>
            <td>Arna Jónsdóttir</td>
            <td>arna.jonsdottir@fieldnotes.example</td>
            <td data-sort-value="2024-09-14">14 Sep 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1043">1043</td>
            <td>Everyday Rights Center</td>
            <td>Legal NGO</td>
            <td>Belgrade</td>
            <td>Serbia</td>
            <td>Milica Petrović</td>
            <td>milica.petrovic@everydayrights.example</td>
            <td data-sort-value="2022-08-24">24 Aug 2022</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1044">1044</td>
            <td>Better Signals Collective</td>
            <td>Media Organization</td>
            <td>Porto</td>
            <td>Portugal</td>
            <td>Tiago Almeida</td>
            <td>tiago.almeida@bettersignals.example</td>
            <td data-sort-value="2023-11-02">2 Nov 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1045">1045</td>
            <td>Plain Systems Cooperative</td>
            <td>Cooperative</td>
            <td>Bologna</td>
            <td>Italy</td>
            <td>Francesca Rinaldi</td>
            <td>francesca.rinaldi@plainsystems.example</td>
            <td data-sort-value="2025-05-05">5 May 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1046">1046</td>
            <td>Public Routes Initiative</td>
            <td>Mobility NGO</td>
            <td>Grenoble</td>
            <td>France</td>
            <td>Manon Lefèvre</td>
            <td>manon.lefevre@publicroutes.example</td>
            <td data-sort-value="2020-02-18">18 Feb 2020</td>
            <td>Inactive</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1047">1047</td>
            <td>Southbank Digital Commons</td>
            <td>Technology Nonprofit</td>
            <td>London</td>
            <td>United Kingdom</td>
            <td>Oliver Bennett</td>
            <td>oliver.bennett@southbankcommons.example</td>
            <td data-sort-value="2023-04-27">27 Apr 2023</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1048">1048</td>
            <td>Quiet Growth Institute</td>
            <td>Research Institute</td>
            <td>Munich</td>
            <td>Germany</td>
            <td>Theresa Bauer</td>
            <td>theresa.bauer@quietgrowth.example</td>
            <td data-sort-value="2024-05-30">30 May 2024</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1049">1049</td>
            <td>Open Care Mapping</td>
            <td>Health Data Group</td>
            <td>Marseille</td>
            <td>France</td>
            <td>Sophie Laurent</td>
            <td>sophie.laurent@opencaremapping.example</td>
            <td data-sort-value="2021-03-11">11 Mar 2021</td>
            <td>Active</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>

        <tr>
            <td data-sort-value="1050">1050</td>
            <td>Local Futures Assembly</td>
            <td>Civic Assembly</td>
            <td>Naples</td>
            <td>Italy</td>
            <td>Antonio Esposito</td>
            <td>antonio.esposito@localfutures.example</td>
            <td data-sort-value="2025-06-24">24 Jun 2025</td>
            <td>Pending</td>
            <td data-table-ignore-search><a href="#" class="button">View</a></td>
        </tr>
        </tbody>
    </table>

    <nav
        data-table-pagination="#organizations-table"
        aria-label="Organizations table pagination">
    </nav>

</section>
