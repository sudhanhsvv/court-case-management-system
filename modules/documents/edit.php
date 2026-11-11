
<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Edit Document';

$errors = [];

// Get document ID.
$documentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$documentId) {
    header('Location: index.php');
    exit;
}

// Fetch existing document.
$stmt = $pdo->prepare("
    SELECT document_id, case_id, document_name,
           stored_filename, file_path, file_type
    FROM documents
    WHERE document_id = ?
");
$stmt->execute([$documentId]);

$document = $stmt->fetch();

if (!$document) {
    header('Location: index.php');
    exit;
}

// Fetch available cases.
$cases = $pdo->query("
    SELECT case_id, case_number, title
    FROM cases
    ORDER BY case_number ASC
")->fetchAll();

$documentName = $document['document_name'];
$caseId = $document['case_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $documentName = trim($_POST['document_name'] ?? '');
    $caseId = filter_var(
        $_POST['case_id'] ?? '',
        FILTER_VALIDATE_INT
    );

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please try again.';
    }

    if ($documentName === '' || strlen($documentName) > 255) {
        $errors[] = 'Enter a valid document name (maximum 255 characters).';
    }

    // Validate selected case.
    if (!$caseId) {
        $errors[] = 'Please select a valid case.';
    } else {
        $caseCheck = $pdo->prepare(
            "SELECT case_id FROM cases WHERE case_id = ?"
        );
        $caseCheck->execute([$caseId]);

        if (!$caseCheck->fetch()) {
            $errors[] = 'Selected case does not exist.';
        }
    }

    $replaceFile = isset($_FILES['document'])
        && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE;

    $newFilename = null;
    $newDestination = null;

    // Validate replacement PDF if one was selected.
    if ($replaceFile) {

        $file = $_FILES['document'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'The replacement file could not be uploaded.';
        } elseif ($file['size'] > 10 * 1024 * 1024) {
            $errors[] = 'File size must not exceed 10 MB.';
        } elseif (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload.';
        } else {

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if ($mimeType !== 'application/pdf') {
                $errors[] = 'Only PDF documents are allowed.';
            }

            $fileHandle = fopen($file['tmp_name'], 'rb');
            $fileHeader = $fileHandle ? fread($fileHandle, 5) : '';

            if ($fileHandle) {
                fclose($fileHandle);
            }

            if ($fileHeader !== '%PDF-') {
                $errors[] = 'The selected file is not a valid PDF.';
            }
        }
    }

    if (empty($errors)) {

        $storageDirectory = __DIR__ . '/../../storage/documents/';

        if ($replaceFile) {

            if (!is_dir($storageDirectory) || !is_writable($storageDirectory)) {
                $errors[] = 'Document storage is not available.';
            } else {

                $newFilename = bin2hex(random_bytes(16)) . '.pdf';
                $newDestination = $storageDirectory . $newFilename;

                if (!move_uploaded_file($file['tmp_name'], $newDestination)) {
                    $errors[] = 'Unable to save the replacement PDF.';
                }
            }
        }

        if (empty($errors)) {

            try {

                if ($replaceFile) {

                    $update = $pdo->prepare("
                        UPDATE documents
                        SET document_name = ?,
                            case_id = ?,
                            stored_filename = ?,
                            file_path = ?,
                            file_type = ?
                        WHERE document_id = ?
                    ");

                    $update->execute([
                        $documentName,
                        $caseId,
                        $newFilename,
                        $newDestination,
                        'application/pdf',
                        $documentId
                    ]);

                } else {

                    $update = $pdo->prepare("
                        UPDATE documents
                        SET document_name = ?, case_id = ?
                        WHERE document_id = ?
                    ");

                    $update->execute([
                        $documentName,
                        $caseId,
                        $documentId
                    ]);
                }

               // Remove old PDF only after successful database update.
                if ($replaceFile) {

    $oldFilename = $document['stored_filename'];

    if (preg_match('/\A[a-f0-9]{32}\.pdf\z/', $oldFilename)) {
        $oldFile = $storageDirectory . $oldFilename;

        if (is_file($oldFile) && $oldFilename !== $newFilename) {
            unlink($oldFile);
        }
    }
}

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {

                if ($newDestination && is_file($newDestination)) {
                    unlink($newDestination);
                }

                error_log('CCMS document update error: ' . $e->getMessage());
                $errors[] = 'Unable to update document information.';
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="form-container">

    <h2>Edit Document</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="client-form">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrf_token()) ?>"
        >

        <div class="form-group">
            <label for="document_name">Document Name</label>

            <input
                type="text"
                id="document_name"
                name="document_name"
                maxlength="255"
                value="<?= e($documentName) ?>"
                required
            >
        </div>

        <div class="form-group">
            <label for="case_id">Select Case</label>

            <select id="case_id" name="case_id" required>
                <?php foreach ($cases as $case): ?>
                    <option
                        value="<?= e($case['case_id']) ?>"
                        <?= (string) $caseId === (string) $case['case_id'] ? 'selected' : '' ?>
                    >
                        <?= e($case['case_number'] . ' - ' . $case['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Current PDF</label>
            <p><?= e($document['stored_filename']) ?></p>
        </div>

        <div class="form-group">
            <label for="document">Replace PDF (Optional, Maximum 10 MB)</label>

            <input
                type="file"
                id="document"
                name="document"
                accept=".pdf,application/pdf"
            >

            <small>Leave empty to keep the existing PDF.</small>
        </div>

        <div class="form-actions">

            <button type="submit" class="btn-primary">
                Update Document
            </button>

            <a href="index.php" class="btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
