<?php

require_once __DIR__ . '/../../includes/auth.php';
require_login();

$pageTitle = 'Edit Client';
$errors = [];

// Get the client ID from the URL.
$clientId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$clientId || $clientId < 1) {
    http_response_code(400);
    exit('Invalid client ID.');
}

// Fetch the existing client.
$query = $pdo->prepare(
    'SELECT * FROM clients WHERE client_id = ?'
);

$query->execute([$clientId]);
$client = $query->fetch();

if (!$client) {
    http_response_code(404);
    exit('Client not found.');
}

// Load existing client details.
$fullName = $client['full_name'];
$phone = $client['phone'];
$email = $client['email'] ?? '';
$address = $client['address'] ?? '';
$status = $client['status'];

// Process the update form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf_token()) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        if ($fullName === '' || mb_strlen($fullName) > 150) {
            $errors[] = 'Enter a valid client name (maximum 150 characters).';
        }

        if (!preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {
            $errors[] = 'Enter a valid phone number.';
        }

        // Limit each phone number to a maximum of 3 clients.
        if (preg_match('/^[0-9+\-\s()]{7,20}$/', $phone)) {

            $checkPhone = $pdo->prepare(
                'SELECT COUNT(*) FROM clients
         WHERE phone = ?
         AND client_id != ?'
            );

            $checkPhone->execute([$phone, $clientId]);

            if ((int) $checkPhone->fetchColumn() >= 3) {
                $errors[] = 'This phone number is already associated with 3 clients. Please use another number.';
            }
        }


        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }

        // Check whether another client already uses this email.
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $checkEmail = $pdo->prepare(
                'SELECT client_id
         FROM clients
         WHERE email = ?
         AND client_id != ?
         LIMIT 1'
            );

            $checkEmail->execute([$email, $clientId]);

            if ($checkEmail->fetch()) {
                $errors[] = 'This email address is already registered to another client.';
            }
        }

        if (!in_array($status, ['Active', 'Inactive'], true)) {
            $errors[] = 'Select a valid client status.';
        }

        // Update the database if validation succeeds.
        if (empty($errors)) {

            $update = $pdo->prepare(
                'UPDATE clients
                 SET full_name = ?,
                     phone = ?,
                     email = ?,
                     address = ?,
                     status = ?
                 WHERE client_id = ?'
            );

            $update->execute([
                $fullName,
                $phone,
                $email !== '' ? $email : null,
                $address !== '' ? $address : null,
                $status,
                $clientId
            ]);

            header('Location: index.php');
            exit;
        }
    }
}

?>


<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="create-client-page">

    <div class="page-heading create-client-heading">
        <div>
            <span class="section-eyebrow">CLIENT DIRECTORY / EDIT RECORD</span>
            <h2>Edit Client Details</h2>
            <p>Update the registered client's information.</p>
        </div>

        <a href="view.php?id=<?= e($clientId) ?>" class="btn-secondary">
            ← Back to Profile
        </a>
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
                <h3>Update Personal Details</h3>
                <p>Modify the information associated with this client.</p>
            </div>

            <div class="form-panel-icon">✎</div>
        </div>

        <form method="POST" action="edit.php?id=<?= e($clientId) ?>" class="client-form create-client-form">

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
                    Update Client
                </button>

                <a href="view.php?id=<?= e($clientId) ?>" class="btn-secondary">
                    Cancel
                </a>
            </div>

        </form>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>