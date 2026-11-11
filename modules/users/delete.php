<?php

require_once __DIR__ . '/../../includes/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

if (!verify_csrf_token()) {
    http_response_code(403);
    exit('Invalid request.');
}

$userId = filter_var(
    $_POST['user_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$userId || $userId < 1) {
    http_response_code(400);
    exit('Invalid user ID.');
}

// Prevent an administrator from deleting their own account.
if ((int) $_SESSION['user_id'] === (int) $userId) {
    http_response_code(403);
    exit('You cannot delete your own account.');
}

try {

    $pdo->beginTransaction();

    // Lock active administrator rows to prevent concurrent
    // deletion or modification of the last active administrator.
    $adminLockStmt = $pdo->query("
        SELECT user_id
        FROM users
        WHERE role = 'Admin'
          AND status = 'Active'
        ORDER BY user_id
        FOR UPDATE
    ");

    $adminLockStmt->fetchAll();

    // Lock the target user row.
    $stmt = $pdo->prepare("
        SELECT user_id, role, status
        FROM users
        WHERE user_id = ?
        FOR UPDATE
    ");

    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        $pdo->rollBack();

        http_response_code(404);
        exit('User not found.');
    }

    // Prevent deletion of the last active administrator.
    if (
        $user['role'] === 'Admin' &&
        $user['status'] === 'Active'
    ) {

        $adminStmt = $pdo->query("
            SELECT COUNT(*)
            FROM users
            WHERE role = 'Admin'
              AND status = 'Active'
        ");

        if ((int) $adminStmt->fetchColumn() <= 1) {
            $pdo->rollBack();

            http_response_code(403);
            exit('The last active administrator cannot be deleted.');
        }
    }

    // Delete the user.
    $deleteStmt = $pdo->prepare(
        "DELETE FROM users WHERE user_id = ?"
    );

    $deleteStmt->execute([$userId]);

    $pdo->commit();

    header('Location: index.php');
    exit;

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('CCMS user deletion error: ' . $e->getMessage());

    http_response_code(409);
    exit('Unable to delete this user. The account may be linked to other records.');
}
