<?php
// Set response type to JSON
header("Content-Type: application/json");

// Include database connection
require_once('../db/connect.php');

// Check if id is provided
if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No blog post ID provided']);
    exit();
}

$id = intval($_GET['id']);

// Fetch single blog post with user information
$sql = "SELECT blog.id, blog.title, blog.content, blog.image, blog.description, users.name, users.email 
    FROM blog 
    JOIN users ON blog.user_id = users.id 
    WHERE blog.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $post = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $post]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Blog post not found']);
}

$stmt->close();
$conn->close();
?>