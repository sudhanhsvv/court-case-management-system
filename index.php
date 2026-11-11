<?php

require_once __DIR__ . '/includes/bootstrap.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
} else {
    header('Location: ' . BASE_URL . '/login.php');
}

exit;