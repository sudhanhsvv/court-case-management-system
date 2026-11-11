<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Reports';

// Case status statistics
$statusStmt = $pdo->query("
    SELECT status, COUNT(*) AS total
    FROM cases
    GROUP BY status
");

$statusResults = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);

$pendingCases = (int) ($statusResults['Pending'] ?? 0);
$inProgressCases = (int) ($statusResults['In Progress'] ?? 0);
$onHoldCases = (int) ($statusResults['On Hold'] ?? 0);
$disposedCases = (int) ($statusResults['Disposed'] ?? 0);

$totalCases = $pendingCases + $inProgressCases + $onHoldCases + $disposedCases;

// Upcoming hearings
$upcomingHearings = (int) $pdo->query("
    SELECT COUNT(*)
    FROM hearings
    WHERE hearing_date >= CURDATE()
")->fetchColumn();

// Cases grouped by type
$typeStmt = $pdo->query("
    SELECT case_type, COUNT(*) AS total
    FROM cases
    GROUP BY case_type
    ORDER BY total DESC, case_type ASC
");

$typeReports = $typeStmt->fetchAll(PDO::FETCH_ASSOC);

// Client-wise case summary
$clientStmt = $pdo->query("
    SELECT
        cl.client_id,
        cl.full_name,
        COUNT(c.case_id) AS total_cases
    FROM clients cl
    LEFT JOIN cases c ON cl.client_id = c.client_id
    GROUP BY cl.client_id, cl.full_name
    ORDER BY total_cases DESC, cl.full_name ASC
");

$clientReports = $clientStmt->fetchAll(PDO::FETCH_ASSOC);

// Chart calculations
$statusData = [
    'Pending' => $pendingCases,
    'In Progress' => $inProgressCases,
    'On Hold' => $onHoldCases,
    'Disposed' => $disposedCases
];

$statusColors = [
    'Pending' => '#C8B38D',
    'In Progress' => '#8BABC5',
    'On Hold' => '#C58F72',
    'Disposed' => '#8BAE99'
];

$chartParts = [];
$currentAngle = 0;

foreach ($statusData as $status => $count) {
    if ($count > 0 && $totalCases > 0) {
        $angle = ($count / $totalCases) * 360;
        $nextAngle = $currentAngle + $angle;

        $chartParts[] = $statusColors[$status] . " {$currentAngle}deg {$nextAngle}deg";
        $currentAngle = $nextAngle;
    }
}

$donutGradient = !empty($chartParts)
    ? 'conic-gradient(' . implode(', ', $chartParts) . ')'
    : 'conic-gradient(#454952 0deg 360deg)';

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="reports-page">

    <div class="page-heading reports-heading">
        <div>
            <span class="section-eyebrow">ANALYTICS & INSIGHTS</span>
            <h2>Reports Overview</h2>
            <p>Monitor case progress, hearing schedules and client activity.</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="dashboard-grid reports-stats-grid">

        <div class="stat-card">
            <p>Total Cases</p>
            <h2><?= $totalCases ?></h2>
        </div>

        <div class="stat-card">
            <p>Pending Cases</p>
            <h2><?= $pendingCases ?></h2>
        </div>

        <div class="stat-card">
            <p>In Progress</p>
            <h2><?= $inProgressCases ?></h2>
        </div>

        <div class="stat-card">
            <p>On Hold</p>
            <h2><?= $onHoldCases ?></h2>
        </div>

        <div class="stat-card">
            <p>Disposed Cases</p>
            <h2><?= $disposedCases ?></h2>
        </div>

        <div class="stat-card">
            <p>Upcoming Hearings</p>
            <h2><?= $upcomingHearings ?></h2>
        </div>

    </div>

    <!-- Charts -->
    <div class="reports-chart-grid">

        <!-- Case Status Doughnut Chart -->
        <div class="dashboard-info report-chart-card">

            <div class="report-section-heading">
                <div>
                    <span class="section-eyebrow">CASE ANALYSIS</span>
                    <h3>Case Status Distribution</h3>
                    <p>Breakdown of all registered cases.</p>
                </div>
            </div>

            <div class="status-chart-layout">

                <div class="status-donut" style="background: <?= e($donutGradient) ?>;">

                    <div class="status-donut-inner">
                        <strong><?= $totalCases ?></strong>
                        <span>Total Cases</span>
                    </div>

                </div>

                <div class="status-legend">

                    <?php foreach ($statusData as $status => $count): ?>

                        <div class="legend-item">

                            <span class="legend-dot" style="background: <?= e($statusColors[$status]) ?>;">
                            </span>

                            <div class="legend-details">
                                <span><?= e($status) ?></span>
                                <strong><?= $count ?></strong>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </div>

        <!-- Cases by Type -->
        <div class="dashboard-info report-chart-card">

            <div class="report-section-heading">
                <div>
                    <span class="section-eyebrow">CASE CATEGORIES</span>
                    <h3>Cases Type</h3>
                    <p>Distribution across legal categories.</p>
                </div>
            </div>

            <?php if (empty($typeReports)): ?>

                <p class="empty-message">No case type records available.</p>

            <?php else: ?>

                <div class="type-chart">

                    <?php foreach ($typeReports as $type): ?>

                        <?php
                        $typeCount = (int) $type['total'];

                        $percentage = $totalCases > 0
                            ? ($typeCount / $totalCases) * 100
                            : 0;
                        ?>

                        <div class="type-chart-item">

                            <div class="type-chart-label">
                                <span><?= e($type['case_type']) ?></span>
                                <strong><?= $typeCount ?></strong>
                            </div>

                            <div class="type-chart-track">
                                <div class="type-chart-fill" style="width: <?= $percentage ?>%;">
                                </div>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

    <!-- Client-wise Summary -->
    <div class="dashboard-info report-table-card">

        <div class="report-section-heading">
            <div>
                <span class="section-eyebrow">CLIENT ACTIVITY</span>
                <h3>Client-wise Case Summary</h3>
                <p>Number of cases registered under each client.</p>
            </div>
        </div>

        <div class="table-container">

            <?php if (empty($clientReports)): ?>

                <p class="empty-message">No client records available.</p>

            <?php else: ?>

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Client Name</th>
                            <th>Total Cases</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($clientReports as $report): ?>

                            <tr>
                                <td><?= e($report['client_id']) ?></td>
                                <td><?= e($report['full_name']) ?></td>
                                <td><?= e($report['total_cases']) ?></td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>