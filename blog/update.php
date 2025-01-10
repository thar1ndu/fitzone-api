<?php
// Set response type to JSON
header("Content-Type: application/json");

// Start session and check if user is logged in
require_once('../auth/isLoggedIn.php');
require_once('../auth/isStaff.php');

// Include database connection
require_once('../db/connect.php');

// Handle PUT request
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($data['id']) || !isset($data['title']) || !isset($data['content']) || !isset($data['image']) || !isset($data['description'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'ID, title, content, image, and description are required']);
        exit();
    }

    // Get user ID from session
    $user_id = $_SESSION['user_id'];

    // Update blog post using prepared statement
    $stmt = $conn->prepare("UPDATE blog SET title = ?, content = ?, image = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ssssii", $data['title'], $data['content'], $data['image'], $data['description'], $data['id'], $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode(['status' => 'success', 'message' => 'Blog post updated successfully']);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'Blog post not found or no changes made']);
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to update blog post']);
    }
    $stmt->close();
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>