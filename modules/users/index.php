<?php

require_once __DIR__ . '/../../includes/auth.php';
require_admin();

$pageTitle = 'User Management';

$stmt = $pdo->query("
    SELECT user_id, full_name, email, role, status, created_at
    FROM users
    ORDER BY user_id DESC
");

$users = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';

?>

<div class="users-page">

    <!-- Page Heading -->
    <div class="page-heading users-heading">

        <div>
            <span class="section-eyebrow">ADMINISTRATION</span>
            <h2>User Directory</h2>
            <p>Manage administrator and staff accounts.</p>
        </div>

        <a href="create.php" class="btn-primary add-user-button">
            <span>+</span> Add New User
        </a>

    </div>


    <!-- User Summary -->
    <div class="users-summary-panel">

        <div class="users-summary-icon">♙</div>

        <div>
            <span class="users-summary-number">
                <?= count($users) ?>
            </span>

            <span class="users-summary-label">
                <?= count($users) === 1 ? 'Registered User' : 'Registered Users' ?>
            </span>
        </div>

    </div>


    <!-- User Records -->
    <div class="table-container users-table-container">

        <div class="users-table-heading">
            <div>
                <h3>Account Records</h3>
                <p>Administrator and staff account details.</p>
            </div>
        </div>

        <?php if (empty($users)): ?>

            <div class="users-empty-state">

                <span class="users-empty-icon">♙</span>

                <h3>No Users Found</h3>

                <p>There are currently no registered user accounts.</p>

                <a href="create.php" class="btn-primary">
                    + Add First User
                </a>

            </div>

        <?php else: ?>

            <div class="users-table-scroll">

                <table class="data-table users-table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($users as $user): ?>

                            <?php
                            $roleClass = strtolower(
                                preg_replace('/[^a-zA-Z0-9]+/', '-', $user['role'])
                            );

                            $statusClass = strtolower(
                                preg_replace('/[^a-zA-Z0-9]+/', '-', $user['status'])
                            );

                            $isCurrentUser =
                                (int) $_SESSION['user_id'] === (int) $user['user_id'];
                            ?>

                            <tr>

                                <td class="user-id-cell">
                                    <?= e($user['user_id']) ?>
                                </td>

                                <td class="user-name-cell">
                                    <div class="user-name-content">

                                        <span class="user-table-avatar">
                                            <?= e(strtoupper(substr($user['full_name'], 0, 1))) ?>
                                        </span>

                                        <span>
                                            <?= e($user['full_name']) ?>

                                            <?php if ($isCurrentUser): ?>
                                                <small class="current-user-label">You</small>
                                            <?php endif; ?>
                                        </span>

                                    </div>
                                </td>

                                <td class="user-email-cell">
                                    <?= e($user['email']) ?>
                                </td>

                                <td>
                                    <span class="user-role-badge role-<?= e($roleClass) ?>">
                                        <?= e($user['role']) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="user-status-badge user-status-<?= e($statusClass) ?>">
                                        <?= e($user['status']) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= e(date('d M Y', strtotime($user['created_at']))) ?>
                                </td>

                                <td class="user-actions">

                                    <a href="edit.php?id=<?= e($user['user_id']) ?>" class="user-action-btn user-edit-btn">
                                        Edit
                                    </a>

                                    <?php if (!$isCurrentUser): ?>

                                        <form method="POST" action="delete.php"
                                            onsubmit="return confirm('Are you sure you want to delete this user?');">

                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                                            <input type="hidden" name="user_id" value="<?= e($user['user_id']) ?>">

                                            <button type="submit" class="user-action-btn user-delete-btn">
                                                Delete
                                            </button>

                                        </form>

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>