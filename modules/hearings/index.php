
<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Hearing Management';

// Fetch all hearings with their associated case details.
$query = $pdo->query(
    "SELECT
        hearings.hearing_id,
        hearings.hearing_date,
        hearings.hearing_time,
        hearings.courtroom,
        hearings.purpose,
        hearings.outcome,
        hearings.next_hearing_date,
        cases.case_number,
        cases.title AS case_title
     FROM hearings
     INNER JOIN cases
        ON hearings.case_id = cases.case_id
     ORDER BY hearings.hearing_date DESC"
);

$hearings = $query->fetchAll();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="hearings-page">

    <!-- Page Heading -->
    <div class="page-heading hearings-heading">

        <div>
            <span class="section-eyebrow">COURT SCHEDULE</span>
            <h2>All Hearings</h2>
            <p>Schedule and monitor court hearings.</p>
        </div>

        <a href="create.php" class="btn-primary schedule-hearing-button">
            <span>+</span> Schedule Hearing
        </a>

    </div>


    <!-- Result Summary -->
    <div class="hearings-summary-panel">

        <div class="hearings-summary-icon">◷</div>

        <div>
            <span class="hearings-summary-number">
                <?= count($hearings) ?>
            </span>
            <span class="hearings-summary-label">
                <?= count($hearings) === 1 ? 'Total Hearing' : 'Total Hearings' ?>
            </span>
        </div>

    </div>


    <!-- Hearing Records -->
    <div class="table-container hearings-table-container">

        <div class="hearings-table-heading">
            <div>
                <h3>Hearing Records</h3>
                <p>All scheduled and recorded court hearings.</p>
            </div>
        </div>

        <?php if (empty($hearings)): ?>

                <div class="hearings-empty-state">

                    <span class="hearings-empty-icon">◷</span>

                    <h3>No Hearings Found</h3>

                    <p>There are currently no hearings scheduled.</p>

                    <a href="create.php" class="btn-primary">
                        + Schedule First Hearing
                    </a>

                </div>

        <?php else: ?>

                <div class="hearings-table-scroll">

                    <table class="data-table hearings-table">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Case Number</th>
                                <th>Case Title</th>
                                <th>Hearing Date</th>
                                <th>Time</th>
                                <th>Courtroom</th>
                                <th>Purpose</th>
                                <th>Next Hearing</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($hearings as $hearing): ?>

                                    <tr>

                                        <td class="hearing-id-cell">
                                            <?= e($hearing['hearing_id']) ?>
                                        </td>

                                        <td class="hearing-case-number">
                                            <?= e($hearing['case_number']) ?>
                                        </td>

                                        <td class="hearing-case-title">
                                            <?= e($hearing['case_title']) ?>
                                        </td>

                                        <td>
                                            <span class="hearing-date">
                                                <?= e(date('d M Y', strtotime($hearing['hearing_date']))) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?= !empty($hearing['hearing_time'])
                                                ? e(date('h:i A', strtotime($hearing['hearing_time'])))
                                                : '<span class="hearing-not-set">Not set</span>' ?>
                                        </td>

                                        <td>
                                            <?= !empty($hearing['courtroom'])
                                                ? e($hearing['courtroom'])
                                                : '<span class="hearing-not-set">Not set</span>' ?>
                                        </td>

                                        <td class="hearing-purpose">
                                            <?= e($hearing['purpose']) ?>
                                        </td>

                                        <td>
                                            <?php if (!empty($hearing['next_hearing_date'])): ?>

                                                    <span class="next-hearing-date">
                                                        <?= e(date('d M Y', strtotime($hearing['next_hearing_date']))) ?>
                                                    </span>

                                            <?php else: ?>

                                                    <span class="hearing-not-set">Not set</span>

                                            <?php endif; ?>
                                        </td>

                                        <td class="hearing-actions">

                                            <a
                                                href="edit.php?id=<?= e($hearing['hearing_id']) ?>"
                                                class="hearing-action-btn hearing-edit-btn">
                                                Edit
                                            </a>

                                            <form
                                                action="delete.php"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to delete this hearing?');">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= e(csrf_token()) ?>">

                                                <input
                                                    type="hidden"
                                                    name="hearing_id"
                                                    value="<?= e($hearing['hearing_id']) ?>">

                                                <button
                                                    type="submit"
                                                    class="hearing-action-btn hearing-delete-btn">
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
