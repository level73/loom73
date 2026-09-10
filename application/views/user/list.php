<main>
    <section>
        <h1><?php echo $title; ?></h1>

        <form role="search">
            <label for="search-input" class="sr-only">Search for data in the table below</label>

            <input
                    type="search"
                    id="search-input"
                    data-table-search="#user-list-table"
                    placeholder="Search users">

            <span class="icon search" aria-hidden="true"></span>
        </form>

        <table
                id="user-list-table"
                data-table
                data-table-page-size="15">

            <thead>
            <tr>
                <th aria-sort="none">
                    <button type="button" data-table-sort="number">ID</button>
                </th>

                <th aria-sort="none">
                    <button type="button" data-table-sort="text">Username</button>
                </th>

                <th aria-sort="none">
                    <button type="button" data-table-sort="text">Email</button>
                </th>

                <th aria-sort="none">
                    <button type="button" data-table-sort="text">Status</button>
                </th>

                <th aria-sort="none">
                    <button type="button" data-table-sort="text">Role</button>
                </th>

                <th aria-sort="none">
                    <button type="button" data-table-sort="date">Last Access</button>
                </th>

                <th aria-sort="none">
                    <button type="button" data-table-sort="date">Created</button>
                </th>

                <th>
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
            </thead>

            <tbody>
            <?php foreach($UserList->data as $user): ?>
                <tr id="user-<?php echo $user->id; ?>">
                    <td
                            class="user-id"
                            data-sort-value="<?php echo $user->id; ?>">
                        <?php echo $user->id; ?>
                    </td>

                    <td>
                        <?php echo $user->username; ?>
                    </td>

                    <td>
                        <?php echo $user->email; ?>
                    </td>

                    <td class="full-cell status-<?php echo $user->status; ?>">
                        <?php echo $user->status; ?>
                    </td>

                    <td class="full-cell role-<?php echo $user->role; ?>">
                        <?php echo $user->role; ?>
                    </td>

                    <td data-sort-value="<?php echo $user->last_access_iso8601; ?>">
                        <?php echo $user->last_access; ?>
                    </td>

                    <td data-sort-value="<?php echo $user->created_at_iso8601; ?>">
                        <?php echo $user->created_at; ?>
                    </td>

                    <td class="full-cell" data-table-ignore-search>
                        <div class="toolbar">
                            <a href="/user/edit/<?php echo $user->id; ?>" class="button">
                                <span class="sr-only">Edit user <?php echo $user->username; ?></span>
                                <i class="stitch stitch--pencil" aria-hidden="true"></i>
                            </a>

                            <button
                                    type="button"
                                    data-dialog-open="user-delete-<?php echo $user->id; ?>"
                                    class="button">
                                <span class="sr-only">Delete User <?php echo $user->username; ?></span>
                                <i class="stitch stitch--bin" aria-hidden="true"></i>
                            </button>

                            <dialog id="user-delete-<?php echo $user->id; ?>" closedby="any">
                                <form method="post" action="/user/delete">
                                    <h3>Delete User</h3>

                                    <p>
                                        Are you sure you want to delete user
                                        <span class="hilite"><?php echo $user->username; ?></span>?
                                    </p>

                                    <p><strong>This operation cannot be undone.</strong></p>

                                    <input type="hidden" name="id" value="<?php echo $user->id; ?>">
                                    <?php csrf(); ?>

                                    <div class="buttons">
                                        <button type="submit" class="modal-button">
                                            Delete User <strong><?php echo $user->username; ?></strong>
                                        </button>

                                        <button
                                                data-dialog-close="user-delete-<?php echo $user->id; ?>"
                                                type="button"
                                                class="modal-button close">
                                            Cancel & Close
                                        </button>
                                    </div>
                                </form>
                            </dialog>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <nav
                data-table-pagination="#user-list-table"
                aria-label="Users table pagination">
        </nav>
    </section>
</main>