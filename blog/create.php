<?php
// Set response type to JSON
header("Content-Type: application/json");

// Start session and check if user is logged in
require_once('../auth/isLoggedIn.php');
require_once('../auth/isStaff.php');

// Include database connection
require_once('../db/connect.php');

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($data['title']) || !isset($data['content']) || !isset($data['image']) || !isset($data['description'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Title, content, image, and description are required']);
        exit();
    }

    // Get user ID from session
    $user_id = $_SESSION['user_id'];

    // Insert blog post using prepared statement
    $stmt = $conn->prepare("INSERT INTO blog (title, content, image, description, user_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $data['title'], $data['content'], $data['image'], $data['description'], $user_id);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(['status' => 'success', 'message' => 'Blog post created successfully']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to create blog post']);
    }
    $stmt->close();
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>