<?php
// Set response type to JSON
header("Content-Type: application/json");

// Start session and check if user is logged in
require_once('../auth/isLoggedIn.php');
require_once('../auth/isStaff.php');

// Include database connection
require_once('../db/connect.php');

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input
    if (!isset($data['id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Blog post ID is required']);
        exit();
    }

    // Get blog post ID
    $post_id = $data['id'];

    // Delete blog post using prepared statement
    $stmt = $conn->prepare("DELETE FROM blog WHERE id = ?");
    $stmt->bind_param("i", $post_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            http_response_code(200); // OK
            echo json_encode(['status' => 'success', 'message' => 'Blog post deleted successfully']);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 'error', 'message' => 'Blog post not found']);
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete blog post']);
    }
    $stmt->close();
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>