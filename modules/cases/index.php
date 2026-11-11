<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Case Management';

// Get search term
$search = trim($_GET['search'] ?? '');

// Fetch cases
if ($search !== '') {

    $query = $pdo->prepare(
        "SELECT
            cases.case_id,
            cases.case_number,
            cases.title,
            cases.case_type,
            cases.advocate_name,
            cases.court_name,
            cases.filing_date,
            cases.status,
            cases.priority,
            clients.full_name AS client_name
         FROM cases
         INNER JOIN clients
            ON cases.client_id = clients.client_id
         WHERE cases.case_number LIKE ?
            OR cases.title LIKE ?
            OR clients.full_name LIKE ?
            OR cases.advocate_name LIKE ?
         ORDER BY cases.case_id DESC"
    );

    $keyword = '%' . $search . '%';

    $query->execute([
        $keyword,
        $keyword,
        $keyword,
        $keyword
    ]);

} else {

    $query = $pdo->query(
        "SELECT
            cases.case_id,
            cases.case_number,
            cases.title,
            cases.case_type,
            cases.advocate_name,
            cases.court_name,
            cases.filing_date,
            cases.status,
            cases.priority,
            clients.full_name AS client_name
         FROM cases
         INNER JOIN clients
            ON cases.client_id = clients.client_id
         ORDER BY cases.case_id DESC"
    );
}

$cases = $query->fetchAll();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="cases-page">

    <!-- Page Heading -->
    <div class="page-heading cases-heading">

        <div>
            <span class="section-eyebrow">CASE DIRECTORY</span>
            <h2>All Cases</h2>
            <p>Manage and monitor registered legal cases.</p>
        </div>

        <a href="create.php" class="btn-primary add-case-button">
            <span>+</span> Add New Case
        </a>

    </div>


    <!-- Search Panel -->
    <div class="search-container cases-search-panel">

        <form method="GET" action="index.php" class="cases-search-form">

            <div class="cases-search-input">

                <span class="cases-search-icon">⌕</span>

                <input type="text" name="search" placeholder="Search by case number, title, client or advocate..."
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

        <div class="cases-result-count">

            <span><?= count($cases) ?></span>

            <?= count($cases) === 1 ? 'case found' : 'cases found' ?>

        </div>

    </div>


    <!-- Cases Table -->
    <div class="table-container cases-table-container">

        <div class="cases-table-heading">

            <div>
                <h3>Case Records</h3>
                <p>Recently registered cases appear first.</p>
            </div>

        </div>

        <?php if (empty($cases)): ?>

            <div class="cases-empty-state">

                <span class="cases-empty-icon">⌕</span>

                <h3>No Cases Found</h3>

                <p>
                    <?= $search !== ''
                        ? 'Try another search term or clear your search.'
                        : 'No cases have been registered yet.' ?>
                </p>

            </div>

        <?php else: ?>

            <div class="cases-table-scroll">

                <table class="data-table cases-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Case Number</th>
                            <th>Case Title</th>
                            <th>Client</th>
                            <th>Case Type</th>
                            <th>Advocate</th>
                            <th>Court</th>
                            <th>Filing Date</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($cases as $case): ?>

                            <?php
                            $statusClass = strtolower(
                                preg_replace('/[^a-zA-Z0-9]+/', '-', $case['status'])
                            );

                            $priorityClass = strtolower(
                                preg_replace('/[^a-zA-Z0-9]+/', '-', $case['priority'])
                            );
                            ?>

                            <tr>

                                <td class="case-id-cell">
                                    <?= e($case['case_id']) ?>
                                </td>

                                <td class="case-number-cell">
                                    <?= e($case['case_number']) ?>
                                </td>

                                <td class="case-title-cell">
                                    <?= e($case['title']) ?>
                                </td>

                                <td>
                                    <?= e($case['client_name']) ?>
                                </td>

                                <td>
                                    <span class="case-type-label">
                                        <?= e($case['case_type']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e($case['advocate_name']) ?>
                                </td>

                                <td>
                                    <?= e($case['court_name']) ?>
                                </td>

                                <td>
                                    <?= e(date('d M Y', strtotime($case['filing_date']))) ?>
                                </td>

                                <td>
                                    <span class="case-status-badge status-<?= e($statusClass) ?>">
                                        <?= e($case['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="case-priority-badge priority-<?= e($priorityClass) ?>">
                                        <?= e($case['priority']) ?>
                                    </span>
                                </td>

                                <td class="case-actions">

                                    <a href="view.php?id=<?= e($case['case_id']) ?>" class="case-action-btn case-view-btn">
                                        View
                                    </a>

                                    <a href="edit.php?id=<?= e($case['case_id']) ?>" class="case-action-btn case-edit-btn">
                                        Edit
                                    </a>

                                    <form action="delete.php" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this case?');">

                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                                        <input type="hidden" name="case_id" value="<?= e($case['case_id']) ?>">

                                        <button type="submit" class="case-action-btn case-delete-btn">
                                            Delete
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>