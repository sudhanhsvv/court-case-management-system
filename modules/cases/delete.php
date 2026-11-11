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

// Validate the case ID.
$caseId = filter_var(
    $_POST['case_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$caseId || $caseId < 1) {
    http_response_code(400);
    exit('Invalid case ID.');
}

// Check whether the case exists.
$checkCase = $pdo->prepare(
    "SELECT case_id FROM cases WHERE case_id = ?"
);
$checkCase->execute([$caseId]);

if (!$checkCase->fetch()) {
    http_response_code(404);
    exit('Case not found.');
}

// Delete the case.
try {

    $delete = $pdo->prepare(
        "DELETE FROM cases WHERE case_id = ?"
    );

    $delete->execute([$caseId]);

    header('Location: index.php');
    exit;

} catch (PDOException $e) {

    // Prevent deletion if hearings or documents are linked.
    if ($e->getCode() === '23000') {
        http_response_code(409);
        exit(
            'This case cannot be deleted because it has associated hearings or documents.'
        );
    }

    error_log('Case deletion error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to delete the case. Please try again.');
}
