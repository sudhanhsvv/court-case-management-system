<?php

require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Dashboard';

require_once __DIR__ . '/includes/header.php';

// Fetch dashboard statistics from MySQL.
$totalClients = (int) $pdo->query(
    "SELECT COUNT(*) FROM clients"
)->fetchColumn();

$totalCases = (int) $pdo->query(
    "SELECT COUNT(*) FROM cases"
)->fetchColumn();

$pendingCases = (int) $pdo->query(
    "SELECT COUNT(*) FROM cases WHERE status IN ('Pending', 'In Progress')"
)->fetchColumn();

$upcomingHearings = (int) $pdo->query(
    "SELECT COUNT(*) FROM hearings WHERE hearing_date >= CURDATE()"
)->fetchColumn();

?>

<div class="welcome-section">
    <h2>Welcome, <?= e($_SESSION['full_name']) ?>!</h2>
    <p>Here is an overview of your legal case management system.</p>
</div>

<div class="dashboard-grid">

    <div class="stat-card">
        <p>Total Clients</p>
        <h2><?= $totalClients ?></h2>
    </div>

    <div class="stat-card">
        <p>Total Cases</p>
        <h2><?= $totalCases ?></h2>
    </div>

    <div class="stat-card">
        <p>Pending Cases</p>
        <h2><?= $pendingCases ?></h2>
    </div>

    <div class="stat-card">
        <p>Upcoming Hearings</p>
        <h2><?= $upcomingHearings ?></h2>
    </div>

</div>

<div class="dashboard-info">
    <h3>System Overview</h3>
    <p>
        Manage clients, monitor case progress, schedule hearings,
        and organize legal documents from one place.
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>