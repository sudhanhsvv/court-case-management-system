<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Document Management';

require_once __DIR__ . '/../../includes/header.php';

$stmt = $pdo->query("
    SELECT
        d.document_id,
        d.document_name,
        d.file_type,
        d.uploaded_at,
        c.case_number,
        c.title AS case_title,
        u.full_name AS uploaded_by
    FROM documents d
    INNER JOIN cases c ON d.case_id = c.case_id
    INNER JOIN users u ON d.uploaded_by = u.user_id
    ORDER BY d.uploaded_at DESC
");

$documents = $stmt->fetchAll();

?>

<div class="page-heading">
    <div>
        <h2>Documents</h2>
        <p>Upload and manage case-related PDF documents.</p>
    </div>

    <a href="upload.php" class="btn-primary">Upload Document</a>
</div>

<div class="table-container">

    <?php if (empty($documents)): ?>

        <p class="empty-message">No documents uploaded yet.</p>

    <?php else: ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Document Name</th>
                    <th>Case Number</th>
                    <th>Case Title</th>
                    <th>Uploaded By</th>
                    <th>Upload Date</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($documents as $document): ?>
                    <tr>
                        <td><?= e($document['document_id']) ?></td>
                        <td><?= e($document['document_name']) ?></td>
                        <td><?= e($document['case_number']) ?></td>
                        <td><?= e($document['case_title']) ?></td>
                        <td><?= e($document['uploaded_by']) ?></td>
                        <td><?= e($document['uploaded_at']) ?></td>

                        <td class="document-actions">

                            <a href="preview.php?id=<?= e($document['document_id']) ?>" class="document-action-btn preview-btn"
                                target="_blank">
                                Preview
                            </a>

                            <a href="edit.php?id=<?= e($document['document_id']) ?>" class="document-action-btn edit-btn">
                                Edit
                            </a>

                            <form action="delete.php" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this document?');">

                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                                <input type="hidden" name="document_id" value="<?= e($document['document_id']) ?>">

                                <button type="submit" class="document-action-btn delete-btn">
                                    Delete
                                </button>
                            </form>

                            <a href="download.php?id=<?= e($document['document_id']) ?>" class="document-download-icon"
                                title="Download Document" aria-label="Download Document">
                                ↓
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>