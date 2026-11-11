<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Add New Case';
$errors = [];

$caseNumber = '';
$title = '';
$caseType = '';
$clientId = '';
$advocateName = '';
$courtName = '';
$filingDate = '';
$status = 'Pending';
$priority = 'Medium';
$description = '';

// Fetch active clients for the dropdown.
$clientQuery = $pdo->query(
    "SELECT client_id, full_name
     FROM clients
     WHERE status = 'Active'
     ORDER BY full_name ASC"
);

$clients = $clientQuery->fetchAll();

// Process the submitted form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {

        $caseNumber = trim($_POST['case_number'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $caseType = trim($_POST['case_type'] ?? '');
        $clientId = $_POST['client_id'] ?? '';
        $advocateName = trim($_POST['advocate_name'] ?? '');
        $courtName = trim($_POST['court_name'] ?? '');
        $filingDate = $_POST['filing_date'] ?? '';
        $status = $_POST['status'] ?? 'Pending';
        $priority = $_POST['priority'] ?? 'Medium';
        $description = trim($_POST['description'] ?? '');

        // Validate case number.
        if ($caseNumber === '' || mb_strlen($caseNumber) > 50) {
            $errors[] = 'Enter a valid case number (maximum 50 characters).';
        }

        // Validate case title.
        if ($title === '' || mb_strlen($title) > 200) {
            $errors[] = 'Enter a valid case title (maximum 200 characters).';
        }

        // Validate case type.
        if ($caseType === '' || mb_strlen($caseType) > 100) {
            $errors[] = 'Enter a valid case type.';
        }

        // Validate client selection.
        $clientId = filter_var($clientId, FILTER_VALIDATE_INT);

        if (!$clientId || $clientId < 1) {
            $errors[] = 'Please select a valid client.';
        } else {
            $checkClient = $pdo->prepare(
                "SELECT client_id FROM clients
                 WHERE client_id = ? AND status = 'Active'"
            );

            $checkClient->execute([$clientId]);

            if (!$checkClient->fetch()) {
                $errors[] = 'The selected client does not exist or is inactive.';
            }
        }

        // Validate advocate and court details.
        if ($advocateName === '' || mb_strlen($advocateName) > 150) {
            $errors[] = 'Enter a valid advocate name.';
        }

        if ($courtName === '' || mb_strlen($courtName) > 200) {
            $errors[] = 'Enter a valid court name.';
        }

        // Validate filing date.
        $dateObject = DateTime::createFromFormat('!Y-m-d', $filingDate);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $filingDate) {
            $errors[] = 'Enter a valid filing date.';
        }

        // Validate status and priority.
        if (!in_array($status, ['Pending', 'In Progress', 'Disposed', 'On Hold'], true)) {
            $errors[] = 'Select a valid case status.';
        }

        if (!in_array($priority, ['Low', 'Medium', 'High'], true)) {
            $errors[] = 'Select a valid priority.';
        }

        // Insert the case into the database.
        if (empty($errors)) {

            try {
                $insert = $pdo->prepare(
                    'INSERT INTO cases
                        (case_number, title, case_type, client_id,
                         advocate_name, court_name, filing_date,
                         status, priority, description)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $insert->execute([
                    $caseNumber,
                    $title,
                    $caseType,
                    $clientId,
                    $advocateName,
                    $courtName,
                    $filingDate,
                    $status,
                    $priority,
                    $description !== '' ? $description : null
                ]);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {
                    $errors[] = 'This case number already exists. Use a different case number.';
                } else {
    error_log('Case creation error: ' . $e->getMessage());
    $errors[] = 'Unable to save the case. Please try again.';
}
            }
        }
    }
}

?>

<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="form-container">

    <div class="page-heading">
        <div>
            <h2>Register New Case</h2>
            <p>Enter the legal case details below.</p>
        </div>

        <a href="index.php" class="btn-secondary">Back to Cases</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-message" role="alert">
            <?php foreach ($errors as $error): ?>
                <p>
                    <?= e($error) ?>
                </p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="create.php" class="client-form">

        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="case_number">Case Number *</label>
            <input type="text" id="case_number" name="case_number" maxlength="50" value="<?= e($caseNumber) ?>"
                required>
        </div>

        <div class="form-group">
            <label for="title">Case Title *</label>
            <input type="text" id="title" name="title" maxlength="200" value="<?= e($title) ?>" required>
        </div>

        <div class="form-group">
            <label for="case_type">Case Type *</label>
            <input type="text" id="case_type" name="case_type" maxlength="100" value="<?= e($caseType) ?>" required>
        </div>

        <div class="form-group">
            <label for="client_id">Select Client *</label>
            <select id="client_id" name="client_id" required>
                <option value="">-- Select Client --</option>

                <?php foreach ($clients as $client): ?>
                    <option value="<?= e($client['client_id']) ?>" <?= (string) $clientId === (string) $client['client_id'] ? 'selected' : '' ?>
                        >
                        <?= e($client['full_name']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label for="advocate_name">Advocate Name *</label>
            <input type="text" id="advocate_name" name="advocate_name" maxlength="150" value="<?= e($advocateName) ?>"
                required>
        </div>

        <div class="form-group">
            <label for="court_name">Court Name *</label>
            <input type="text" id="court_name" name="court_name" maxlength="200" value="<?= e($courtName) ?>" required>
        </div>

        <div class="form-group">
            <label for="filing_date">Filing Date *</label>
            <input type="date" id="filing_date" name="filing_date" value="<?= e($filingDate) ?>" required>
        </div>

        <div class="form-group">
            <label for="status">Case Status</label>
            <select id="status" name="status">
                <?php foreach (['Pending', 'In Progress', 'Disposed', 'On Hold'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <?php foreach (['Low', 'Medium', 'High'] as $option): ?>
                    <option value="<?= e($option) ?>" <?= $priority === $option ? 'selected' : '' ?>>
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Case Description</label>
            <textarea id="description" name="description" rows="4"><?= e($description) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Save Case
            </button>

            <a href="index.php" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>