<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$documentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$documentId || $documentId < 1) {
    http_response_code(400);
    exit('Invalid document ID.');
}

$stmt = $pdo->prepare("
    SELECT document_name, stored_filename
    FROM documents
    WHERE document_id = ?
");

$stmt->execute([$documentId]);
$document = $stmt->fetch();

if (!$document) {
    http_response_code(404);
    exit('Document not found.');
}

// Validate the server-controlled filename.
$filename = $document['stored_filename'];

if (!preg_match('/\A[a-zA-Z0-9_-]+\.pdf\z/', $filename)) {
    http_response_code(400);
    exit('Invalid stored filename.');
}

// Locate the PDF.
$filePath = __DIR__ . '/../../storage/documents/' . $filename;

if (!is_file($filePath) || !is_readable($filePath)) {
    http_response_code(404);
    exit('Document file not found.');
}

// Display PDF inside the browser instead of downloading it.
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="document.pdf"');
header('Content-Length: ' . filesize($filePath));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

readfile($filePath);
exit;
