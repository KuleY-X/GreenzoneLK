<?php
require_once 'config.php';

// Verify user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Clear all session data
logout_user();
?>
