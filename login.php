<?php

require_once __DIR__ . '/includes/bootstrap.php';

// If the user is already logged in, open the dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token.
    if (!verify_csrf_token()) {
        $error = 'Invalid request. Please refresh the page and try again.';
    } else {

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            !is_string($password) ||
            $password === ''
        ) {
            $error = 'Invalid email or password.';
        } else {

            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

            // Check email/IP lockout.
            $attemptQuery = $pdo->prepare(
                'SELECT locked_until
                 FROM login_attempts
                 WHERE email = ? AND ip_address = ?'
            );

            $attemptQuery->execute([$email, $ipAddress]);
            $attempt = $attemptQuery->fetch();

            $isEmailLocked = $attempt
                && $attempt['locked_until'] !== null
                && strtotime($attempt['locked_until']) > time();

            // Check IP-wide lockout.
            $ipQuery = $pdo->prepare(
                'SELECT locked_until
                 FROM ip_login_attempts
                 WHERE ip_address = ?'
            );

            $ipQuery->execute([$ipAddress]);
            $ipAttempt = $ipQuery->fetch();

            $isIpLocked = $ipAttempt
                && $ipAttempt['locked_until'] !== null
                && strtotime($ipAttempt['locked_until']) > time();

            if ($isEmailLocked || $isIpLocked) {

                $error = 'Too many failed attempts. Please try again later.';

            } else {

                // Find the account.
                $userQuery = $pdo->prepare(
                    'SELECT user_id, full_name, email, password, role, status
                     FROM users
                     WHERE email = ?
                     LIMIT 1'
                );

                $userQuery->execute([$email]);
                $user = $userQuery->fetch();

                // Verify password and account status.
                if (
                    $user &&
                    $user['status'] === 'Active' &&
                    password_verify($password, $user['password'])
                ) {

                    // Clear failed attempts after successful login.
                    $deleteAttempts = $pdo->prepare(
                        'DELETE FROM login_attempts
                         WHERE email = ? AND ip_address = ?'
                    );

                    $deleteAttempts->execute([$email, $ipAddress]);

                    $deleteIpAttempts = $pdo->prepare(
                        'DELETE FROM ip_login_attempts
                         WHERE ip_address = ?'
                    );

                    $deleteIpAttempts->execute([$ipAddress]);

                    // Generate a new session ID.
                    session_regenerate_id(true);

                    // Store authenticated user information.
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    header('Location: dashboard.php');
                    exit;

                } else {

                    // Atomically update the email/IP failed-attempt counter.
                    $saveAttempt = $pdo->prepare(
                        'INSERT INTO login_attempts
                            (email, ip_address, failed_attempts, last_attempt_at, locked_until)
                         VALUES (?, ?, 1, NOW(), NULL)
                         ON DUPLICATE KEY UPDATE
                            locked_until = CASE
                                WHEN (
                                    CASE
                                        WHEN last_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                                        THEN 1
                                        ELSE failed_attempts + 1
                                    END
                                ) >= 5
                                THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                                ELSE NULL
                            END,
                            failed_attempts = CASE
                                WHEN last_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                                THEN 1
                                ELSE failed_attempts + 1
                            END,
                            last_attempt_at = NOW()'
                    );

                    $saveAttempt->execute([$email, $ipAddress]);

                    // Atomically update the IP-wide failed-attempt counter.
                    $saveIpAttempt = $pdo->prepare(
                        'INSERT INTO ip_login_attempts
                            (ip_address, failed_attempts, last_attempt_at, locked_until)
                         VALUES (?, 1, NOW(), NULL)
                         ON DUPLICATE KEY UPDATE
                            locked_until = CASE
                                WHEN (
                                    CASE
                                        WHEN last_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                                        THEN 1
                                        ELSE failed_attempts + 1
                                    END
                                ) >= 20
                                THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                                ELSE NULL
                            END,
                            failed_attempts = CASE
                                WHEN last_attempt_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
                                THEN 1
                                ELSE failed_attempts + 1
                            END,
                            last_attempt_at = NOW()'
                    );

                    $saveIpAttempt->execute([$ipAddress]);

                    $error = 'Invalid email or password.';
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Court Case Management System</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>

<body>

    <main class="login-page">

        <!-- Left: Login section -->
        <section class="login-section">

            <div class="brand">
                <div class="brand-icon" aria-hidden="true">⚖</div>
                <div>
                    <h2>CCMS</h2>
                    <p>COURT CASE MANAGEMENT SYSTEM</p>
                </div>
            </div>

            <div class="login-content">

                <p class="eyebrow">WELCOME BACK</p>

                <h1>Justice begins<br>with <span>organisation.</span></h1>

                <p class="login-description">
                    Sign in to access your workspace and manage your legal proceedings.
                </p>

                <?php if ($error !== ''): ?>
                    <div class="login-error" role="alert">
                        <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="login-form">

                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email address" required
                            autocomplete="username">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>

                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                required autocomplete="current-password">

                            <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password"
                                aria-pressed="false">
                                Show
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="login-button">
                        Sign In <span aria-hidden="true">→</span>
                    </button>

                </form>

                <p class="login-footer">
                    Secure access · Court Case Management System
                </p>

            </div>

        </section>

        <!-- Right: Cinematic 3D section -->
        <section class="visual-section">

            <div class="visual-overlay"></div>



            <div class="scene-container" id="justice-scene">
                <img src="<?= BASE_URL ?>/assets/images/lady-justice-scene.png"
                    alt="Bronze Lady Justice statue holding scales and a sword" class="lady-justice-image">
            </div>


        </section>

    </main>

    <script src="<?= BASE_URL ?>/assets/js/login.js"></script>

</body>

</html>