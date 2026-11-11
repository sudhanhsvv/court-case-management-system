
<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Upload Document';

$errors = [];
$cases = $pdo->query("
    SELECT case_id, case_number, title
    FROM cases
    ORDER BY case_number ASC
")->fetchAll();

$documentName = '';
$caseId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $documentName = trim($_POST['document_name'] ?? '');
    $caseId = $_POST['case_id'] ?? '';

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please try again.';
    }

    if ($documentName === '' || strlen($documentName) > 255) {
        $errors[] = 'Enter a valid document name (maximum 255 characters).';
    }

    $caseId = filter_var($caseId, FILTER_VALIDATE_INT);

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

    if (
        !isset($_FILES['document']) ||
        $_FILES['document']['error'] !== UPLOAD_ERR_OK
    ) {
        $errors[] = 'Please select a PDF document to upload.';
    } else {
        $file = $_FILES['document'];

        if ($file['size'] > 10 * 1024 * 1024) {
            $errors[] = 'File size must not exceed 10 MB.';
        }

        if (!is_uploaded_file($file['tmp_name'])) {
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

        $storedFilename = bin2hex(random_bytes(16)) . '.pdf';
        $destination = $storageDirectory . $storedFilename;

        if (!is_dir($storageDirectory) || !is_writable($storageDirectory)) {
            $errors[] = 'Document storage is not available.';
        } elseif (!move_uploaded_file($file['tmp_name'], $destination)) {
            $errors[] = 'Unable to save the uploaded document.';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO documents
                    (case_id, uploaded_by, document_name, stored_filename, file_path, file_type)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $caseId,
                    $_SESSION['user_id'],
                    $documentName,
                    $storedFilename,
                    $destination,
                    'application/pdf'
                ]);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                if (is_file($destination)) {
                    unlink($destination);
                }

                error_log('CCMS document upload error: ' . $e->getMessage());
                $errors[] = 'Unable to save document information.';
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="form-container">

    <h2>Upload Document</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cases)): ?>
        <p class="empty-message">
            Create a case before uploading its documents.
        </p>
    <?php else: ?>

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
                    <option value="">Choose a case</option>

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
                <label for="document">Select PDF (Maximum 10 MB)</label>
                <input
                    type="file"
                    id="document"
                    name="document"
                    accept=".pdf,application/pdf"
                    required
                >
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Upload Document
                </button>

                <a href="index.php" class="btn-secondary">Cancel</a>
            </div>

        </form>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
