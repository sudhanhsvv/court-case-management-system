<?php

require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$pageTitle = 'Add New User';

$errors = [];

$fullName = '';
$email = '';
$role = 'Staff';
$status = 'Active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'Staff';
    $status = $_POST['status'] ?? 'Active';

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please try again.';
    }

    if ($fullName === '' || strlen($fullName) > 100) {
        $errors[] = 'Enter a valid name (maximum 100 characters).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
        $errors[] = 'Enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must contain at least 8 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!in_array($role, ['Admin', 'Staff'], true)) {
        $errors[] = 'Invalid user role.';
    }

    if (!in_array($status, ['Active', 'Inactive'], true)) {
        $errors[] = 'Invalid account status.';
    }

    if (empty($errors)) {

        $checkStmt = $pdo->prepare(
            "SELECT user_id FROM users WHERE email = ?"
        );

        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {
            $errors[] = 'An account with this email already exists.';
        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {
                $insertStmt = $pdo->prepare("
                    INSERT INTO users
                    (full_name, email, password, role, status)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $insertStmt->execute([
                    $fullName,
                    $email,
                    $hashedPassword,
                    $role,
                    $status
                ]);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                error_log('CCMS user creation error: ' . $e->getMessage());
                $errors[] = 'Unable to create the user account.';
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="form-container">

    <h2>Add New User</h2>

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
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="8" required>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
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
                Create User
            </button>

            <a href="index.php" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>