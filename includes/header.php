<?php
require_once __DIR__ . '/auth.php';
require_login();

$pageTitle = $pageTitle ?? 'Dashboard';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= e($pageTitle) ?> | Court Case Management System
    </title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>

    <div class="app-container">
        <aside class="sidebar">

            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="brand-icon">C</div>
                <div class="brand-details">
                    <h2>CCMS</h2>
                    <p>Legal Case Management</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="sidebar-nav">

                <a href="<?= BASE_URL ?>/dashboard.php"
                    class="nav-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                    <span class="nav-icon">⌂</span>
                    <span class="nav-label">Dashboard</span>
                </a>

                <a href="<?= BASE_URL ?>/modules/clients/index.php"
                    class="nav-link <?= $currentPage === 'index.php' && str_contains($_SERVER['PHP_SELF'], '/clients/') ? 'active' : '' ?>">
                    <span class="nav-icon">♙</span>
                    <span class="nav-label">Clients</span>
                </a>

                <a href="<?= BASE_URL ?>/modules/cases/index.php"
                    class="nav-link <?= $currentPage === 'index.php' && str_contains($_SERVER['PHP_SELF'], '/cases/') ? 'active' : '' ?>">
                    <span class="nav-icon">▤</span>
                    <span class="nav-label">Cases</span>
                </a>

                <a href="<?= BASE_URL ?>/modules/hearings/index.php"
                    class="nav-link <?= $currentPage === 'index.php' && str_contains($_SERVER['PHP_SELF'], '/hearings/') ? 'active' : '' ?>">
                    <span class="nav-icon">▦</span>
                    <span class="nav-label">Hearings</span>
                </a>

                <a href="<?= BASE_URL ?>/modules/documents/index.php"
                    class="nav-link <?= $currentPage === 'index.php' && str_contains($_SERVER['PHP_SELF'], '/documents/') ? 'active' : '' ?>">
                    <span class="nav-icon">▧</span>
                    <span class="nav-label">Documents</span>
                </a>

                <a href="<?= BASE_URL ?>/modules/reports/index.php"
                    class="nav-link <?= $currentPage === 'index.php' && str_contains($_SERVER['PHP_SELF'], '/reports/') ? 'active' : '' ?>">
                    <span class="nav-icon">▥</span>
                    <span class="nav-label">Reports</span>
                </a>

                <?php if (($_SESSION['role'] ?? '') === 'Admin'): ?>
                    <a href="<?= BASE_URL ?>/modules/users/index.php"
                        class="nav-link <?= $currentPage === 'index.php' && str_contains($_SERVER['PHP_SELF'], '/users/') ? 'active' : '' ?>">
                        <span class="nav-icon">♙</span>
                        <span class="nav-label">User Management</span>
                    </a>
                <?php endif; ?>

            </nav>

            <!-- User Profile -->
            <div class="sidebar-user">

                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                </div>

                <div class="user-details">
                    <p><?= e($_SESSION['full_name'] ?? 'User') ?></p>
                    <small><?= e($_SESSION['role'] ?? '') ?></small>
                </div>

                <form action="<?= BASE_URL ?>/logout.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <button type="submit" class="logout-button" title="Logout">
                        ↗
                    </button>
                </form>

            </div>

        </aside>

        <main class="main-content">

            <header class="topbar">

                <div class="topbar-heading">
                    <span class="topbar-label">LEGAL WORKSPACE</span>
                    <h1><?= e($pageTitle) ?></h1>
                </div>

                <div class="topbar-actions">

                    <div class="topbar-user">
                        <div class="topbar-avatar">
                            <?= strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1)) ?>
                        </div>

                        <div class="topbar-user-details">
                            <span><?= e($_SESSION['full_name'] ?? 'User') ?></span>
                            <small><?= e($_SESSION['role'] ?? '') ?></small>
                        </div>
                    </div>

                </div>

            </header>


            <section class="page-content">