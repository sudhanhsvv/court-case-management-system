<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Schedule Hearing';
$errors = [];

$caseId = '';
$hearingDate = '';
$hearingTime = '';
$courtroom = '';
$purpose = '';
$outcome = '';
$nextHearingDate = '';
$remarks = '';

// Fetch registered cases for the dropdown.
$caseQuery = $pdo->query(
    "SELECT case_id, case_number, title
     FROM cases
     ORDER BY case_id DESC"
);

$cases = $caseQuery->fetchAll();
$selectedCaseId = filter_input(INPUT_GET, 'case_id', FILTER_VALIDATE_INT);

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
        } elseif ($hearingDate < date('Y-m-d')) {
            $errors[] = 'Hearing date cannot be in the past.';
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
        // Insert hearing if validation succeeds.
        if (empty($errors)) {

            try {
                $insert = $pdo->prepare(
                    'INSERT INTO hearings
                (case_id, hearing_date, hearing_time, courtroom,
                 purpose, outcome, next_hearing_date, remarks)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );

                $insert->execute([
                    $caseId,
                    $hearingDate,
                    $hearingTime !== '' ? $hearingTime : null,
                    $courtroom !== '' ? $courtroom : null,
                    $purpose,
                    $outcome !== '' ? $outcome : null,
                    $nextHearingDate !== '' ? $nextHearingDate : null,
                    $remarks !== '' ? $remarks : null
                ]);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {
                error_log('Hearing creation error: ' . $e->getMessage());
                $errors[] = 'Unable to schedule the hearing. Please try again.';
            }
        }
    }
}

?>


<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="form-container">

    <div class="page-heading">
        <div>
            <h2>Schedule New Hearing</h2>
            <p>Enter the hearing details below.</p>
        </div>

        <a href="index.php" class="btn-secondary">Back to Hearings</a>
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
            <label for="case_id">Select Case *</label>
            <select id="case_id" name="case_id" required>
                <option value="">-- Select Case --</option>

                <?php foreach ($cases as $case): ?>
                    <option value="<?= e($case['case_id']) ?>" <?= (string) $selectedCaseId === (string) $case['case_id'] ? 'selected' : '' ?>>
                        <?= e($case['case_number']) ?> -
                        <?= e($case['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class=" form-group">
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
                Save Hearing
            </button>

            <a href="index.php" class="btn-secondary">Cancel</a>
        </div>

    </form>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>