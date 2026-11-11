<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$caseId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$caseId || $caseId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch case and associated client.
$stmt = $pdo->prepare("
    SELECT cases.*, clients.full_name AS client_name,
           clients.phone AS client_phone,
           clients.email AS client_email
    FROM cases
    INNER JOIN clients ON cases.client_id = clients.client_id
    WHERE cases.case_id = ?
");
$stmt->execute([$caseId]);
$case = $stmt->fetch();

if (!$case) {
    http_response_code(404);
    exit('Case not found.');
}

// Fetch hearing history.
$stmt = $pdo->prepare("
    SELECT hearing_id, hearing_date, hearing_time,
           courtroom, purpose, outcome, next_hearing_date
    FROM hearings
    WHERE case_id = ?
    ORDER BY hearing_date DESC
");
$stmt->execute([$caseId]);
$hearings = $stmt->fetchAll();

// Fetch associated documents.
$stmt = $pdo->prepare("
    SELECT document_id, document_name, file_type, uploaded_at
    FROM documents
    WHERE case_id = ?
    ORDER BY uploaded_at DESC
");
$stmt->execute([$caseId]);
$documents = $stmt->fetchAll();

$pageTitle = 'Case Details';

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="page-heading">
    <div>
        <h2><?= e($case['title']) ?></h2>
        <p>Case Number: <?= e($case['case_number']) ?></p>
    </div>

    <a href="edit.php?id=<?= e($case['case_id']) ?>" class="btn-primary">Edit Case</a>
</div>

<div class="dashboard-info">
    <h3>Case Information</h3>

    <p><strong>Case Type:</strong> <?= e($case['case_type']) ?></p>
    <p><strong>Client:</strong>
        <a href="../clients/view.php?id=<?= e($case['client_id']) ?>">
            <?= e($case['client_name']) ?>
        </a>
    </p>
    <p><strong>Advocate:</strong> <?= e($case['advocate_name']) ?></p>
    <p><strong>Court:</strong> <?= e($case['court_name']) ?></p>
    <p><strong>Filing Date:</strong> <?= e($case['filing_date']) ?></p>
    <p><strong>Status:</strong> <?= e($case['status']) ?></p>
    <p><strong>Priority:</strong> <?= e($case['priority']) ?></p>
    <p><strong>Description:</strong>
        <?= e($case['description'] ?: 'Not provided') ?>
    </p>
</div>

<div class="page-heading">
    <div>
        <h3>Hearing History</h3>
        <p>All hearings associated with this case.</p>
    </div>

    <a href="../hearings/create.php?case_id=<?= e($case['case_id']) ?>" class="btn-primary">Schedule Hearing</a>
</div>

<div class="table-container">

    <?php if (empty($hearings)): ?>

        <p class="empty-message">No hearings recorded for this case.</p>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Courtroom</th>
                    <th>Purpose</th>
                    <th>Outcome</th>
                    <th>Next Hearing</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($hearings as $hearing): ?>
                    <tr>
                        <td><?= e($hearing['hearing_date']) ?></td>
                        <td><?= e($hearing['hearing_time'] ?: '-') ?></td>
                        <td><?= e($hearing['courtroom'] ?: '-') ?></td>
                        <td><?= e($hearing['purpose']) ?></td>
                        <td><?= e($hearing['outcome'] ?: '-') ?></td>
                        <td><?= e($hearing['next_hearing_date'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<div class="page-heading">
    <div>
        <h3>Case Documents</h3>
        <p>Documents associated with this case.</p>
    </div>

    <a href="../documents/upload.php?case_id=<?= e($case['case_id']) ?>" class="btn-primary">Upload Document</a>
</div>

<div class="table-container">

    <?php if (empty($documents)): ?>

        <p class="empty-message">No documents uploaded for this case.</p>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Document Name</th>
                    <th>File Type</th>
                    <th>Uploaded At</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($documents as $document): ?>
                    <tr>
                        <td><?= e($document['document_name']) ?></td>
                        <td><?= e($document['file_type']) ?></td>
                        <td><?= e($document['uploaded_at']) ?></td>
                        <td>
                            <a href="../documents/download.php?id=<?= e($document['document_id']) ?>">
                                Download
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<a href="index.php" class="btn-secondary">Back to Cases</a>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>