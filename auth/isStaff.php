<?php
// Check if user is logged in
if ($_SESSION['user_role'] != 'staff' && $_SESSION['user_role'] != 'admin') {
    http_response_code(403); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Permission Denied']);
    exit();
}
?>