<?php
// Set reply type to JSON
header("Content-Type: application/json");

// Include database connection
require_once('../db/connect.php');

// Fetch all blog posts with user information
$sql = "SELECT blog.id, blog.title, blog.content, blog.image, blog.description, users.name, users.email 
    FROM blog JOIN users ON blog.user_id = users.id
    ORDER BY blog.id DESC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $posts = [];
    while ($row = $result->fetch_assoc()) {
    $posts[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $posts]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No blog posts found']);
}

$conn->close();
?>