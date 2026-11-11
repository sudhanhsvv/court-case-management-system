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
    exit('Invalid request. Please try again.');
}

// Validate the hearing ID.
$hearingId = filter_var(
    $_POST['hearing_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$hearingId || $hearingId < 1) {
    http_response_code(400);
    exit('Invalid hearing ID.');
}

try {
    // Check whether the hearing exists.
    $checkHearing = $pdo->prepare(
        "SELECT hearing_id FROM hearings WHERE hearing_id = ?"
    );
    $checkHearing->execute([$hearingId]);

    if (!$checkHearing->fetch()) {
        http_response_code(404);
        exit('Hearing not found.');
    }

    // Delete the hearing.
    $delete = $pdo->prepare(
        "DELETE FROM hearings WHERE hearing_id = ?"
    );

    $delete->execute([$hearingId]);

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    error_log('Hearing deletion error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to delete the hearing. Please try again.');
}
