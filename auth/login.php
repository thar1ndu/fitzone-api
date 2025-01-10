<?php
// Start the session
session_start();

// Include database connection
require_once('../db/connect.php');

// Set response type to JSON
header("Content-Type: application/json");

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
        exit();
    }

    // Check user credentials using prepared statement
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $data['email']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        $stmt->close();
        exit();
    }

    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($data['password'], $user['password'])) {
        // Remove password from response
        unset($user['password']);

            // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
        
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }
    
    $stmt->close();
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>