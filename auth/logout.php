<?php
// Start the session
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Set response type to JSON
header("Content-Type: application/json");

// Return success response
http_response_code(200);
echo json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
?>