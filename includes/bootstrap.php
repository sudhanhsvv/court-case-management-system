<?php

// Base URL of the CCMS application.
define('BASE_URL', '/Court%20Case%20Management%20System');

// Apply basic HTTP security headers.
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Prevent direct access to this file.
if (basename($_SERVER['SCRIPT_FILENAME']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Direct access not allowed.');
}

// Configure secure session cookies.
// HTTPS will be enabled when the application is deployed.
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax'
]);

// Start the session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load the database connection.
require_once __DIR__ . '/../config/database.php';

// Escape output before displaying user-provided data.
function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Generate a CSRF token.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Verify a submitted CSRF token.
function verify_csrf_token(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? '';

    return is_string($submittedToken)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submittedToken);
}
