<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

if (!verify_csrf_token()) {
    http_response_code(403);
    exit('Invalid request.');
}

$documentId = filter_var(
    $_POST['document_id'] ?? '',
    FILTER_VALIDATE_INT
);

if (!$documentId || $documentId < 1) {
    http_response_code(400);
    exit('Invalid document ID.');
}

$stmt = $pdo->prepare("
    SELECT stored_filename
    FROM documents
    WHERE document_id = ?
");

$stmt->execute([$documentId]);
$document = $stmt->fetch();

if (!$document) {
    http_response_code(404);
    exit('Document not found.');
}

$filename = $document['stored_filename'];

if (!preg_match('/\A[a-f0-9]{32}\.pdf\z/', $filename)) {
    http_response_code(400);
    exit('Invalid stored filename.');
}

$filePath = __DIR__ . '/../../storage/documents/' . $filename;

try {
    $deleteStmt = $pdo->prepare(
        "DELETE FROM documents WHERE document_id = ?"
    );

    $deleteStmt->execute([$documentId]);

    if (is_file($filePath) && !unlink($filePath)) {
        error_log('CCMS: Unable to remove document file: ' . $filename);
    }

    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    error_log('CCMS document deletion error: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to delete the document.');
}
