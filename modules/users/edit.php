<?php

require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$pageTitle = 'Edit User';

$errors = [];

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$userId || $userId < 1) {
    http_response_code(400);
    exit('Invalid user ID.');
}

$stmt = $pdo->prepare("
    SELECT user_id, full_name, email, role, status
    FROM users
    WHERE user_id = ?
");

$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    exit('User not found.');
}

$fullName = $user['full_name'];
$email = $user['email'];
$role = $user['role'];
$status = $user['status'];

$isCurrentUser = ((int) $_SESSION['user_id'] === (int) $userId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please try again.';
    }

    if ($fullName === '' || strlen($fullName) > 100) {
        $errors[] = 'Enter a valid name (maximum 100 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Enter a valid email address.';
    }

    if (!in_array($role, ['Admin', 'Staff'], true)) {
        $errors[] = 'Invalid user role.';
    }

    if (!in_array($status, ['Active', 'Inactive'], true)) {
        $errors[] = 'Invalid account status.';
    }

    // Prevent an administrator from disabling or demoting their own account.
    if ($isCurrentUser && ($role !== 'Admin' || $status !== 'Active')) {
        $errors[] = 'You cannot change your own role or deactivate your account.';
    }

    // Check whether another account already uses this email.
    if (empty($errors)) {

        $checkStmt = $pdo->prepare("
            SELECT user_id
            FROM users
            WHERE email = ? AND user_id != ?
        ");

        $checkStmt->execute([$email, $userId]);

        if ($checkStmt->fetch()) {
            $errors[] = 'Another account already uses this email address.';
        }
    }

    if (empty($errors)) {

        try {

            // Start a transaction for administrator account protection.
            $pdo->beginTransaction();

            // Lock all currently active administrators.
            // This serializes concurrent administrator changes.
            $adminLockStmt = $pdo->query("
                SELECT user_id
                FROM users
                WHERE role = 'Admin'
                  AND status = 'Active'
                ORDER BY user_id
                FOR UPDATE
            ");

            $adminLockStmt->fetchAll();

            // Fetch and lock the latest version of the target account.
            $currentStmt = $pdo->prepare("
                SELECT role, status
                FROM users
                WHERE user_id = ?
                FOR UPDATE
            ");

            $currentStmt->execute([$userId]);
            $currentUser = $currentStmt->fetch();

            if (!$currentUser) {

                $pdo->rollBack();
                $errors[] = 'User not found.';

            } else {

                // Prevent removal of the last active administrator.
                if (
                    $currentUser['role'] === 'Admin' &&
                    $currentUser['status'] === 'Active' &&
                    ($role !== 'Admin' || $status !== 'Active')
                ) {

                    $adminStmt = $pdo->query("
                        SELECT COUNT(*)
                        FROM users
                        WHERE role = 'Admin'
                          AND status = 'Active'
                    ");

                    if ((int) $adminStmt->fetchColumn() <= 1) {
                        $pdo->rollBack();

                        $errors[] = 'The last active administrator cannot be demoted or deactivated.';
                    }
                }

                if (empty($errors)) {

                    $updateStmt = $pdo->prepare("
                        UPDATE users
                        SET full_name = ?, email = ?, role = ?, status = ?
                        WHERE user_id = ?
                    ");

                    $updateStmt->execute([
                        $fullName,
                        $email,
                        $role,
                        $status,
                        $userId
                    ]);

                    $pdo->commit();

                    // Refresh session details when editing the current account.
                    if ($isCurrentUser) {
                        $_SESSION['full_name'] = $fullName;
                    }

                    header('Location: index.php');
                    exit;
                }
            }

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('CCMS user update error: ' . $e->getMessage());
            $errors[] = 'Unable to update the user account.';
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="form-container">

    <h2>Edit User</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p>
                    <?= e($error) ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="client-form">

        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" maxlength="100" value="<?= e($fullName) ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" maxlength="150" value="<?= e($email) ?>" required>
        </div>

        <div class="form-group">
            <label for="role">User Role</label>
            <select id="role" name="role" required>
                <option value="Staff" <?= $role === 'Staff' ? 'selected' : '' ?>>
                    Staff
                </option>
                <option value="Admin" <?= $role === 'Admin' ? 'selected' : '' ?>>
                    Admin
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="status">Account Status</label>
            <select id="status" name="status" required>
                <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>
                    Active
                </option>
                <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>
                    Inactive
                </option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Update User
            </button>

            <a href="index.php" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>