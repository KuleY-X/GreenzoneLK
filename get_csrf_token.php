<?php
require_once 'config.php';

header('Content-Type: application/json');

// Generate and return CSRF token
$csrf_token = generate_csrf_token();

echo json_encode([
    'csrf_token' => $csrf_token,
    'success' => true
]);
?>
