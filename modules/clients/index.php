<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Client Management';

require_once __DIR__ . '/../../includes/header.php';

// Get the search keyword.
$search = trim($_GET['search'] ?? '');

// Fetch clients based on the search keyword.
if ($search !== '') {

    $query = $pdo->prepare(
        "SELECT client_id, full_name, phone, email, status, created_at
         FROM clients
         WHERE full_name LIKE ?
            OR phone LIKE ?
            OR CAST(client_id AS CHAR) LIKE ?
         ORDER BY client_id DESC"
    );

    $keyword = '%' . $search . '%';

    $query->execute([
        $keyword,
        $keyword,
        $keyword
    ]);

} else {

    $query = $pdo->query(
        "SELECT client_id, full_name, phone, email, status, created_at
         FROM clients
         ORDER BY client_id DESC"
    );

}

$clients = $query->fetchAll();

?>

<div class="clients-page">

    <!-- Page heading -->
    <div class="page-heading clients-heading">
        <div>
            <span class="section-eyebrow">CLIENT DIRECTORY</span>
            <h2>All Clients</h2>
            <p>Manage your registered clients and their information.</p>
        </div>

        <a href="create.php" class="btn-primary add-client-button">
            <span>+</span> Add New Client
        </a>
    </div>

    <!-- Search panel -->
    <div class="search-container clients-search-panel">

        <form method="GET" action="index.php" class="search-form">

            <div class="search-input-wrapper">
                <span class="search-icon">⌕</span>

                <input type="text" name="search" placeholder="Search by client name, phone or ID..."
                    value="<?= e($search) ?>">
            </div>

            <button type="submit" class="btn-primary">
                Search
            </button>

            <?php if ($search !== ''): ?>
                <a href="index.php" class="btn-secondary">
                    Clear
                </a>
            <?php endif; ?>

        </form>

        <div class="clients-result-count">
            <span><?= count($clients) ?></span>
            <?= count($clients) === 1 ? 'client found' : 'clients found' ?>
        </div>

    </div>

    <!-- Clients table -->
    <div class="table-container clients-table-container">

        <div class="table-heading">
            <div>
                <h3>Client Records</h3>
                <p>Recently registered clients appear first.</p>
            </div>
        </div>

        <div class="clients-table-scroll">

            <table class="data-table clients-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (empty($clients)): ?>

                        <tr>
                            <td colspan="7" class="empty-message">
                                <?= $search !== ''
                                    ? 'No clients match your search.'
                                    : 'No clients registered yet.' ?>
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($clients as $client): ?>

                            <?php
                            $statusClass = strtolower(
                                preg_replace('/[^a-zA-Z0-9]+/', '-', $client['status'])
                            );
                            ?>

                            <tr>

                                <td>
                                    <span class="client-id">
                                        #<?= e($client['client_id']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="client-name">
                                        <?= e($client['full_name']) ?>
                                    </span>
                                </td>

                                <td><?= e($client['phone']) ?></td>

                                <td><?= e($client['email'] ?? '') ?></td>

                                <td>
                                    <span class="status-badge status-<?= e($statusClass) ?>">
                                        <?= e($client['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date('d M Y', strtotime($client['created_at']))) ?>
                                </td>

                                <td>
                                    <div class="client-actions">

                                        <a href="view.php?id=<?= e($client['client_id']) ?>"
                                            class="action-link action-view">View</a>

                                        <a href="edit.php?id=<?= e($client['client_id']) ?>"
                                            class="action-link action-edit">Edit</a>

                                        <form action="delete.php" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this client?');">
                                            <input type="hidden" name="client_id" value="<?= e($client['client_id']) ?>">

                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                                            <button type="submit" class="action-link action-delete">Delete</button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>