<?php

require_once __DIR__ . '/includes/auth.php';

// Only accept POST requests for logout.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify the CSRF token.
if (!verify_csrf_token()) {
    http_response_code(403);
    exit('Invalid request.');
}

// Remove all session data.
$_SESSION = [];

// Remove the session cookie using the original cookie settings.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

// Destroy the server-side session.
session_destroy();

// Return to the login page.
header('Location: login.php');
exit;
