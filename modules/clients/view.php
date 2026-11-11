<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$clientId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$clientId || $clientId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch client details.
$stmt = $pdo->prepare("
    SELECT *
    FROM clients
    WHERE client_id = ?
");
$stmt->execute([$clientId]);
$client = $stmt->fetch();

if (!$client) {
    http_response_code(404);
    exit('Client not found.');
}

// Fetch all cases belonging to this client.
$stmt = $pdo->prepare("
    SELECT case_id, case_number, title, case_type,
           court_name, filing_date, status, priority
    FROM cases
    WHERE client_id = ?
    ORDER BY case_id DESC
");
$stmt->execute([$clientId]);
$cases = $stmt->fetchAll();

$pageTitle = 'Client Profile';

require_once __DIR__ . '/../../includes/header.php';

$statusClass = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $client['status']));

?>

<div class="client-profile-page">

    <div class="page-heading client-profile-heading">
        <div>
            <span class="section-eyebrow">CLIENT DIRECTORY / PROFILE</span>
            <h2><?= e($client['full_name']) ?></h2>
            <p>Client ID: #<?= e($client['client_id']) ?></p>
        </div>

        <div class="profile-heading-actions">
            <a href="index.php" class="btn-secondary">← Back</a>
            <a href="edit.php?id=<?= e($client['client_id']) ?>" class="btn-primary">
                Edit Client
            </a>
        </div>
    </div>

    <div class="client-profile-card">

        <div class="profile-card-header">
            <div class="profile-avatar">
                <?= strtoupper(substr($client['full_name'], 0, 1)) ?>
            </div>

            <div class="profile-identity">
                <h3><?= e($client['full_name']) ?></h3>
                <p>Registered Client</p>
            </div>

            <span class="client-status-badge status-<?= e($statusClass) ?>">
                <?= e($client['status']) ?>
            </span>
        </div>

        <div class="profile-details-heading">
            <span class="section-eyebrow">PERSONAL INFORMATION</span>
            <h3>Contact Details</h3>
        </div>

        <div class="profile-details-grid">

            <div class="profile-detail-item">
                <span>Phone Number</span>
                <p><?= e($client['phone']) ?></p>
            </div>

            <div class="profile-detail-item">
                <span>Email Address</span>
                <p><?= e($client['email'] ?: 'Not provided') ?></p>
            </div>

            <div class="profile-detail-item profile-address-item">
                <span>Address</span>
                <p><?= e($client['address'] ?: 'Not provided') ?></p>
            </div>

            <div class="profile-detail-item">
                <span>Registration Date</span>
                <p><?= e($client['created_at']) ?></p>
            </div>

            <div class="profile-detail-item">
                <span>Client ID</span>
                <p>#<?= e($client['client_id']) ?></p>
            </div>

        </div>

    </div>

    <div class="profile-cases-section">

        <div class="page-heading profile-cases-heading">
            <div>
                <span class="section-eyebrow">LEGAL RECORDS</span>
                <h3>Associated Cases</h3>
                <p>Cases registered under this client.</p>
            </div>

            <div class="case-count">
                <?= count($cases) ?> <?= count($cases) === 1 ? 'Case' : 'Cases' ?>
            </div>
        </div>

        <div class="table-container profile-cases-table">

            <?php if (empty($cases)): ?>

                <div class="profile-empty-state">
                    <div class="empty-state-icon">§</div>
                    <h3>No Associated Cases</h3>
                    <p>There are currently no cases registered under this client.</p>
                </div>

            <?php else: ?>

                <div class="clients-table-scroll">
                    <table class="data-table clients-table">
                        <thead>
                            <tr>
                                <th>Case Number</th>
                                <th>Title</th>
                                <th>Case Type</th>
                                <th>Court</th>
                                <th>Filing Date</th>
                                <th>Status</th>
                                <th>Priority</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($cases as $case): ?>
                                <tr>
                                    <td class="client-name">
                                        <?= e($case['case_number']) ?>
                                    </td>
                                    <td><?= e($case['title']) ?></td>
                                    <td><?= e($case['case_type']) ?></td>
                                    <td><?= e($case['court_name']) ?></td>
                                    <td><?= e($case['filing_date']) ?></td>
                                    <td><?= e($case['status']) ?></td>
                                    <td><?= e($case['priority']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>