<?php

require_once __DIR__ . '/bootstrap.php';

// Check whether a user is logged in and their account is still active.
function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    global $pdo;

    // Fetch the user's latest account details from the database.
    $stmt = $pdo->prepare("
        SELECT role, status
        FROM users
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Reject deleted or inactive accounts.
    if (!$user || $user['status'] !== 'Active') {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }

    // Keep the session role synchronized with the database.
    $_SESSION['role'] = $user['role'];
}

// Check whether the logged-in user has the required role.
function require_role(string $requiredRole): void
{
    require_login();

    if (($_SESSION['role'] ?? '') !== $requiredRole) {
        http_response_code(403);
        exit('Access denied. You do not have permission to access this page.');
    }
}

// Check whether the user is an administrator.
function require_admin(): void
{
    require_role('Admin');
}
