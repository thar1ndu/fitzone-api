<?php
// Include database connection
require_once('../db/connect.php');

// Set response type to JSON
header("Content-Type: application/json");

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit();
    }

    // Check if email already exists using prepared statement
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $data['email']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
        $checkStmt->close();
        exit();
    }
    $checkStmt->close();

    // Hash password
    $password = password_hash($data['password'], PASSWORD_BCRYPT);
    $role = 'user';

    // Insert user using prepared statement
    $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $insertStmt->bind_param("ssss", $data['name'], $data['email'], $password, $role);

    if ($insertStmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to register user']);
    }
    $insertStmt->close();
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>
