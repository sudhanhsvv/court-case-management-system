<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Edit Hearing';
$errors = [];

// Get and validate the hearing ID from the URL.
$hearingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$hearingId || $hearingId < 1) {
    http_response_code(400);
    exit('Invalid hearing ID.');
}

// Fetch the existing hearing.
$hearingQuery = $pdo->prepare(
    "SELECT * FROM hearings WHERE hearing_id = ?"
);
$hearingQuery->execute([$hearingId]);
$hearing = $hearingQuery->fetch();

if (!$hearing) {
    http_response_code(404);
    exit('Hearing not found.');
}

// Fetch registered cases for the dropdown.
$caseQuery = $pdo->query(
    "SELECT case_id, case_number, title
     FROM cases
     ORDER BY case_id DESC"
);

$cases = $caseQuery->fetchAll();

// Load existing hearing details.
$caseId = $hearing['case_id'];
$hearingDate = $hearing['hearing_date'];
$hearingTime = !empty($hearing['hearing_time'])
    ? substr($hearing['hearing_time'], 0, 5)
    : '';
$courtroom = $hearing['courtroom'] ?? '';
$purpose = $hearing['purpose'];
$outcome = $hearing['outcome'] ?? '';
$nextHearingDate = $hearing['next_hearing_date'] ?? '';
$remarks = $hearing['remarks'] ?? '';

// Process the submitted form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {

        $caseId = filter_var(
            $_POST['case_id'] ?? '',
            FILTER_VALIDATE_INT
        );

        $hearingDate = $_POST['hearing_date'] ?? '';
        $hearingTime = $_POST['hearing_time'] ?? '';
        $courtroom = trim($_POST['courtroom'] ?? '');
        $purpose = trim($_POST['purpose'] ?? '');
        $outcome = trim($_POST['outcome'] ?? '');
        $nextHearingDate = $_POST['next_hearing_date'] ?? '';
        $remarks = trim($_POST['remarks'] ?? '');

        // Validate selected case.
        if (!$caseId || $caseId < 1) {
            $errors[] = 'Please select a valid case.';
        } else {
            $checkCase = $pdo->prepare(
                "SELECT case_id FROM cases WHERE case_id = ?"
            );

            $checkCase->execute([$caseId]);

            if (!$checkCase->fetch()) {
                $errors[] = 'The selected case does not exist.';
            }
        }

        // Validate hearing date.
        $dateObject = DateTime::createFromFormat('!Y-m-d', $hearingDate);

        if (!$dateObject || $dateObject->format('Y-m-d') !== $hearingDate) {
            $errors[] = 'Enter a valid hearing date.';
        }

        // Validate optional hearing time.
        if ($hearingTime !== '') {
            $timeObject = DateTime::createFromFormat('!H:i', $hearingTime);

            if (!$timeObject || $timeObject->format('H:i') !== $hearingTime) {
                $errors[] = 'Enter a valid hearing time.';
            }
        }

        // Validate courtroom and purpose.
        if (mb_strlen($courtroom) > 100) {
            $errors[] = 'Courtroom cannot exceed 100 characters.';
        }

        if ($purpose === '' || mb_strlen($purpose) > 255) {
            $errors[] = 'Enter a valid hearing purpose.';
        }

        // Validate optional next hearing date.
if ($nextHearingDate !== '') {
    $nextDateObject = DateTime::createFromFormat(
        '!Y-m-d',
        $nextHearingDate
    );

    if (
        !$nextDateObject ||
        $nextDateObject->format('Y-m-d') !== $nextHearingDate
    ) {
        $errors[] = 'Enter a valid next hearing date.';
    } elseif (
        $dateObject &&
        $nextHearingDate < $hearingDate
    ) {
        $errors[] = 'Next hearing date cannot be earlier than the current hearing date.';
    }
}

       // Update hearing if validation succeeds.
if (empty($errors)) {

    try {
        $update = $pdo->prepare(
            'UPDATE hearings SET
                case_id = ?,
                hearing_date = ?,
                hearing_time = ?,
                courtroom = ?,
                purpose = ?,
                outcome = ?,
                next_hearing_date = ?,
                remarks = ?
             WHERE hearing_id = ?'
        );

        $update->execute([
            $caseId,
            $hearingDate,
            $hearingTime !== '' ? $hearingTime : null,
            $courtroom !== '' ? $courtroom : null,
            $purpose,
            $outcome !== '' ? $outcome : null,
            $nextHearingDate !== '' ? $nextHearingDate : null,
            $remarks !== '' ? $remarks : null,
            $hearingId
        ]);

        header('Location: index.php');
        exit;

    } catch (PDOException $e) {
        error_log('Hearing update error: ' . $e->getMessage());
        $errors[] = 'Unable to update the hearing. Please try again.';
    }
}
        }
    }


?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="form-container">

    <div class="page-heading">
        <div>
            <h2>Edit Hearing</h2>
            <p>Update the hearing details below.</p>
        </div>

        <a href="index.php" class="btn-secondary">Back to Hearings</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-message" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="edit.php?id=<?= e($hearingId) ?>" class="client-form">

        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form-group">
            <label for="case_id">Select Case *</label>
            <select id="case_id" name="case_id" required>
                <option value="">-- Select Case --</option>

                <?php foreach ($cases as $case): ?>
                    <option value="<?= e($case['case_id']) ?>" <?= (string) $caseId === (string) $case['case_id'] ? 'selected' : '' ?>
                        >
                        <?= e($case['case_number']) ?> -
                        <?= e($case['title']) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <div class="form-group">
            <label for="hearing_date">Hearing Date *</label>
            <input type="date" id="hearing_date" name="hearing_date" value="<?= e($hearingDate) ?>" required>
        </div>

        <div class="form-group">
            <label for="hearing_time">Hearing Time</label>
            <input type="time" id="hearing_time" name="hearing_time" value="<?= e($hearingTime) ?>">
        </div>

        <div class="form-group">
            <label for="courtroom">Courtroom</label>
            <input type="text" id="courtroom" name="courtroom" maxlength="100" value="<?= e($courtroom) ?>">
        </div>

        <div class="form-group">
            <label for="purpose">Hearing Purpose *</label>
            <input type="text" id="purpose" name="purpose" maxlength="255" value="<?= e($purpose) ?>" required>
        </div>

        <div class="form-group">
            <label for="outcome">Hearing Outcome</label>
            <textarea id="outcome" name="outcome" rows="3"><?= e($outcome) ?></textarea>
        </div>

        <div class="form-group">
            <label for="next_hearing_date">Next Hearing Date</label>
            <input type="date" id="next_hearing_date" name="next_hearing_date" value="<?= e($nextHearingDate) ?>">
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea id="remarks" name="remarks" rows="3"><?= e($remarks) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">
                Update Hearing
            </button>

            <a href="index.php" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

