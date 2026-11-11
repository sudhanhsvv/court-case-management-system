<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Add New Client';
$errors = [];

$fullName = '';
$phone = '';
$email = '';
$address = '';
$status = 'Active';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {

        $fullName = is_string($_POST['full_name'] ?? null) ? trim($_POST['full_name']) : '';
        $phone = is_string($_POST['phone'] ?? null) ? trim($_POST['phone']) : '';
        $email = is_string($_POST['email'] ?? null) ? trim($_POST['email']) : '';
        $address = is_string($_POST['address'] ?? null) ? trim($_POST['address']) : '';
        $status = is_string($_POST['status'] ?? null) ? $_POST['status'] : '';

        if ($fullName === '' || mb_strlen($fullName) > 150) {
            $errors[] = 'Enter a valid client name (maximum 150 characters).';
        }

        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $errors[] = 'Enter a valid phone number.';
        }

        if (preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {

            $checkPhone = $pdo->prepare(
                'SELECT COUNT(*) FROM clients WHERE phone = ?'
            );

            $checkPhone->execute([$phone]);

            if ((int) $checkPhone->fetchColumn() >= 3) {
                $errors[] = 'This phone number is already associated with 3 clients. Please use another number.';
            }
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $checkEmail = $pdo->prepare(
                'SELECT client_id FROM clients WHERE email = ? LIMIT 1'
            );

            $checkEmail->execute([$email]);

            if ($checkEmail->fetch()) {
                $errors[] = 'This email address is already registered to another client.';
            }
        }

        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $errors[] = 'Select a valid client status.';
        }

        if (empty($errors)) {

            $insert = $pdo->prepare(
                'INSERT INTO clients
                    (full_name, phone, email, address, status)
                 VALUES (?, ?, ?, ?, ?)'
            );

            try {
                $insert->execute([
                    $fullName,
                    $phone,
                    $email !== '' ? $email : null,
                    $address !== '' ? $address : null,
                    $status
                ]);

                header('Location: index.php');
                exit;

            } catch (PDOException $e) {

                if ($e->getCode() === '23000') {
                    $errors[] = 'This email address is already registered to another client.';
                } else {
                    error_log('Client creation error: ' . $e->getMessage());
                    $errors[] = 'Unable to save the client. Please try again.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="create-client-page">

    <div class="page-heading create-client-heading">
        <div>
            <span class="section-eyebrow">CLIENT DIRECTORY / NEW RECORD</span>
            <h2>Register New Client</h2>
            <p>Add a new client and their contact information to the system.</p>
        </div>

        <a href="index.php" class="btn-secondary">← Back to Clients</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="error-message create-client-errors" role="alert">
            <?php foreach ($errors as $error): ?>
                <p><?= e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="client-form-panel">

        <div class="client-form-header">
            <div>
                <span class="section-eyebrow">CLIENT INFORMATION</span>
                <h3>Personal Details</h3>
                <p>Fields marked with * are required.</p>
            </div>

            <div class="form-panel-icon">＋</div>
        </div>

        <form method="POST" action="create.php" class="client-form create-client-form">

            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <div class="create-form-grid">

                <div class="form-group">
                    <label for="full_name">Full Name <span>*</span></label>
                    <input type="text" id="full_name" name="full_name" maxlength="150"
                        placeholder="Enter client's full name" value="<?= e($fullName) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number <span>*</span></label>
                    <input type="tel" id="phone" name="phone" maxlength="20" placeholder="Enter phone number"
                        value="<?= e($phone) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" maxlength="150" placeholder="example@email.com"
                        value="<?= e($email) ?>">
                </div>

                <div class="form-group">
                    <label for="status">Client Status</label>
                    <select id="status" name="status">
                        <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>
                            Active
                        </option>

                        <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="form-group address-field">
                    <label for="address">Residential / Office Address</label>
                    <textarea id="address" name="address" rows="4"
                        placeholder="Enter the client's complete address"><?= e($address) ?></textarea>
                </div>

            </div>

            <div class="form-actions create-client-actions">
                <button type="submit" class="btn-primary">
                    Save Client
                </button>

                <a href="index.php" class="btn-secondary">Cancel</a>
            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>