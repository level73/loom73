<main id="main-content">
    <section class="grid">
        <div>
            <h1>Loom73 Tables <span class="accent">Example</span></h1>
            <hr />
            <p>All data presented on this page is fictional and seeded randomly.</p>
            <form role="search" class="table-search">
                <label for="authors-search" class="sr-only">Search authors</label>
                <input type="search" id="authors-search" data-table-search="#authors-table" placeholder="Search authors">
                <span class="stitch stitch--search" aria-hidden="true"></span>
            </form>
            <div class="table-scroll" role="region" aria-label="Example table" tabindex="0">
                <table id="authors-table" data-table data-table-page-size="10">
                <thead>
                <tr>
                    <th>
                        <button type="button" data-table-sort="number">ID</button>
                    </th>
                    <th>
                        <button type="button" data-table-sort="text">First name</button>
                    </th>
                    <th>
                        <button type="button" data-table-sort="text">Surname</button>
                    </th>
                    <th>
                        <button type="button" data-table-sort="number">Papers published</button>
                    </th>
                    <th>
                        <button type="button" data-table-sort="date">Registration date</button>
                    </th>
                    <th>View profile</th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td data-sort-value="1">1</td>
                    <td>Amelia</td>
                    <td>Hart</td>
                    <td data-sort-value="12">12</td>
                    <td data-sort-value="2021-03-14">14 Mar 2021</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Amelia Hart">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="2">2</td>
                    <td>Jonas</td>
                    <td>Mercer</td>
                    <td data-sort-value="7">7</td>
                    <td data-sort-value="2020-11-02">2 Nov 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Jonas Mercer">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="3">3</td>
                    <td>Leila</td>
                    <td>Navarro</td>
                    <td data-sort-value="18">18</td>
                    <td data-sort-value="2019-06-27">27 Jun 2019</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Leila Navarro">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="4">4</td>
                    <td>Marcus</td>
                    <td>Reed</td>
                    <td data-sort-value="4">4</td>
                    <td data-sort-value="2023-01-18">18 Jan 2023</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Marcus Reed">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="5">5</td>
                    <td>Sofia</td>
                    <td>Bennett</td>
                    <td data-sort-value="21">21</td>
                    <td data-sort-value="2018-09-11">11 Sep 2018</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Sofia Bennett">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="6">6</td>
                    <td>Elliot</td>
                    <td>Clarke</td>
                    <td data-sort-value="9">9</td>
                    <td data-sort-value="2022-05-06">6 May 2022</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Elliot Clarke">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="7">7</td>
                    <td>Maya</td>
                    <td>Singh</td>
                    <td data-sort-value="15">15</td>
                    <td data-sort-value="2020-02-23">23 Feb 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Maya Singh">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="8">8</td>
                    <td>Theo</td>
                    <td>Wallace</td>
                    <td data-sort-value="3">3</td>
                    <td data-sort-value="2024-04-15">15 Apr 2024</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Theo Wallace">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="9">9</td>
                    <td>Nina</td>
                    <td>Petrov</td>
                    <td data-sort-value="11">11</td>
                    <td data-sort-value="2021-08-30">30 Aug 2021</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Nina Petrov">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="10">10</td>
                    <td>Adrian</td>
                    <td>Foster</td>
                    <td data-sort-value="6">6</td>
                    <td data-sort-value="2022-12-09">9 Dec 2022</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Adrian Foster">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="11">11</td>
                    <td>Elena</td>
                    <td>Rossi</td>
                    <td data-sort-value="27">27</td>
                    <td data-sort-value="2017-05-19">19 May 2017</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Elena Rossi">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="12">12</td>
                    <td>Caleb</td>
                    <td>Morgan</td>
                    <td data-sort-value="2">2</td>
                    <td data-sort-value="2025-01-12">12 Jan 2025</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Caleb Morgan">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="13">13</td>
                    <td>Hannah</td>
                    <td>Kim</td>
                    <td data-sort-value="14">14</td>
                    <td data-sort-value="2020-07-04">4 Jul 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Hannah Kim">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="14">14</td>
                    <td>Victor</td>
                    <td>Almeida</td>
                    <td data-sort-value="19">19</td>
                    <td data-sort-value="2019-10-21">21 Oct 2019</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Victor Almeida">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="15">15</td>
                    <td>Isla</td>
                    <td>Grant</td>
                    <td data-sort-value="8">8</td>
                    <td data-sort-value="2023-06-01">1 Jun 2023</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Isla Grant">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="16">16</td>
                    <td>Noah</td>
                    <td>Laurent</td>
                    <td data-sort-value="23">23</td>
                    <td data-sort-value="2018-12-16">16 Dec 2018</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Noah Laurent">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="17">17</td>
                    <td>Zoe</td>
                    <td>Carter</td>
                    <td data-sort-value="5">5</td>
                    <td data-sort-value="2024-02-07">7 Feb 2024</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Zoe Carter">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="18">18</td>
                    <td>Gabriel</td>
                    <td>Moretti</td>
                    <td data-sort-value="31">31</td>
                    <td data-sort-value="2016-03-28">28 Mar 2016</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Gabriel Moretti">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="19">19</td>
                    <td>Freya</td>
                    <td>Davies</td>
                    <td data-sort-value="10">10</td>
                    <td data-sort-value="2021-11-13">13 Nov 2021</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Freya Davies">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="20">20</td>
                    <td>Oscar</td>
                    <td>Nguyen</td>
                    <td data-sort-value="17">17</td>
                    <td data-sort-value="2019-01-25">25 Jan 2019</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Oscar Nguyen">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="21">21</td>
                    <td>Clara</td>
                    <td>West</td>
                    <td data-sort-value="1">1</td>
                    <td data-sort-value="2025-05-09">9 May 2025</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Clara West">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="22">22</td>
                    <td>Julian</td>
                    <td>Santos</td>
                    <td data-sort-value="13">13</td>
                    <td data-sort-value="2020-04-18">18 Apr 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Julian Santos">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="23">23</td>
                    <td>Aisha</td>
                    <td>Rahman</td>
                    <td data-sort-value="20">20</td>
                    <td data-sort-value="2018-07-22">22 Jul 2018</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Aisha Rahman">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="24">24</td>
                    <td>Daniel</td>
                    <td>Brooks</td>
                    <td data-sort-value="6">6</td>
                    <td data-sort-value="2022-09-03">3 Sep 2022</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Daniel Brooks">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="25">25</td>
                    <td>Lucia</td>
                    <td>Ferreira</td>
                    <td data-sort-value="16">16</td>
                    <td data-sort-value="2020-12-28">28 Dec 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Lucia Ferreira">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="26">26</td>
                    <td>Samuel</td>
                    <td>Price</td>
                    <td data-sort-value="25">25</td>
                    <td data-sort-value="2017-08-10">10 Aug 2017</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Samuel Price">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="27">27</td>
                    <td>Eva</td>
                    <td>Schneider</td>
                    <td data-sort-value="9">9</td>
                    <td data-sort-value="2023-03-17">17 Mar 2023</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Eva Schneider">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="28">28</td>
                    <td>Leo</td>
                    <td>Harrison</td>
                    <td data-sort-value="12">12</td>
                    <td data-sort-value="2021-01-05">5 Jan 2021</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Leo Harrison">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="29">29</td>
                    <td>Camille</td>
                    <td>Dubois</td>
                    <td data-sort-value="28">28</td>
                    <td data-sort-value="2016-11-14">14 Nov 2016</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Camille Dubois">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="30">30</td>
                    <td>Isaac</td>
                    <td>Turner</td>
                    <td data-sort-value="3">3</td>
                    <td data-sort-value="2024-07-29">29 Jul 2024</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Isaac Turner">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="31">31</td>
                    <td>Layla</td>
                    <td>Okafor</td>
                    <td data-sort-value="22">22</td>
                    <td data-sort-value="2018-02-20">20 Feb 2018</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Layla Okafor">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="32">32</td>
                    <td>Henry</td>
                    <td>Collins</td>
                    <td data-sort-value="7">7</td>
                    <td data-sort-value="2022-06-24">24 Jun 2022</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Henry Collins">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="33">33</td>
                    <td>Marta</td>
                    <td>Kowalska</td>
                    <td data-sort-value="14">14</td>
                    <td data-sort-value="2020-09-08">8 Sep 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Marta Kowalska">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="34">34</td>
                    <td>Felix</td>
                    <td>Andersen</td>
                    <td data-sort-value="18">18</td>
                    <td data-sort-value="2019-04-12">12 Apr 2019</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Felix Andersen">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="35">35</td>
                    <td>Naomi</td>
                    <td>Evans</td>
                    <td data-sort-value="5">5</td>
                    <td data-sort-value="2023-10-31">31 Oct 2023</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Naomi Evans">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="36">36</td>
                    <td>Rafael</td>
                    <td>Torres</td>
                    <td data-sort-value="24">24</td>
                    <td data-sort-value="2017-12-03">3 Dec 2017</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Rafael Torres">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="37">37</td>
                    <td>Chloe</td>
                    <td>Martin</td>
                    <td data-sort-value="11">11</td>
                    <td data-sort-value="2021-07-16">16 Jul 2021</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Chloe Martin">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="38">38</td>
                    <td>Mateo</td>
                    <td>Rivera</td>
                    <td data-sort-value="8">8</td>
                    <td data-sort-value="2022-01-27">27 Jan 2022</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Mateo Rivera">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="39">39</td>
                    <td>Ines</td>
                    <td>Moreau</td>
                    <td data-sort-value="29">29</td>
                    <td data-sort-value="2016-06-07">7 Jun 2016</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Ines Moreau">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="40">40</td>
                    <td>Owen</td>
                    <td>Murphy</td>
                    <td data-sort-value="4">4</td>
                    <td data-sort-value="2024-09-19">19 Sep 2024</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Owen Murphy">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="41">41</td>
                    <td>Yara</td>
                    <td>Haddad</td>
                    <td data-sort-value="17">17</td>
                    <td data-sort-value="2020-05-26">26 May 2020</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Yara Haddad">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="42">42</td>
                    <td>Arthur</td>
                    <td>Green</td>
                    <td data-sort-value="13">13</td>
                    <td data-sort-value="2021-09-22">22 Sep 2021</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Arthur Green">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>

                <tr>
                    <td data-sort-value="43">43</td>
                    <td>Selma</td>
                    <td>Jensen</td>
                    <td data-sort-value="26">26</td>
                    <td data-sort-value="2017-02-11">11 Feb 2017</td>
                    <td>
                        <button type="button" class="button" aria-label="View profile for Selma Jensen">
                            <span class="stitch stitch--view" aria-hidden="true"></span>
                        </button>
                    </td>
                </tr>
                </tbody>
            </table>
            </div>
            <nav
                data-table-pagination="#authors-table"
                aria-label="Authors table pagination">
            </nav>
        </div>
    </section>
    <section class="grid">
        <div>
            <h2>References</h2>
            <hr />
            <a class="button hollow" href="https://github.com/level73/loom73/blob/main/documentation/frontend/tables.md" target="_blank">Read the docs <i class="stitch stitch--arrow"></i></a>
        </div>
    </section>
</main>