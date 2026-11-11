<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

// Only allow POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// Verify the CSRF token.
if (!verify_csrf_token()) {
    http_response_code(403);
    exit('Invalid request.');
}

// Get and validate the client ID.
$clientId = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);

if (!$clientId || $clientId < 1) {
    http_response_code(400);
    exit('Invalid client ID.');
}

// Delete the client using a prepared statement.
$delete = $pdo->prepare(
    'DELETE FROM clients WHERE client_id = ?'
);

try {
    $delete->execute([$clientId]);
} catch (PDOException $e) {
    // A client with existing cases cannot be deleted.
    if ($e->getCode() === '23000') {
        http_response_code(409);
        exit('This client cannot be deleted because they have associated cases.');
    }

    error_log('Client deletion error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to delete the client. Please try again.');
}

// Return to the Clients page.
header('Location: index.php');
exit;