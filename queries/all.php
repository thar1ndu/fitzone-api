<?php
// Set response type to JSON
header("Content-Type: application/json");

// Start session and check if user is logged in
require_once('../auth/isLoggedIn.php');

// Include database connection
require_once('../db/connect.php');

// Get user role from session
$user_role = $_SESSION['user_role'];
$user_id = $_SESSION['user_id'];

// Prepare SQL query based on user role
if ($user_role == 'staff' || $user_role == 'admin') {
    $sql = "SELECT queries.id, queries.subject, queries.message, users.name AS user_name 
            FROM queries 
            JOIN users ON queries.user_id = users.id";
} else {
    $sql = "SELECT queries.id, queries.subject, queries.message, users.name AS user_name 
            FROM queries 
            JOIN users ON queries.user_id = users.id 
            WHERE queries.user_id = ?";
}

// Prepare and execute statement
$stmt = $conn->prepare($sql);

if ($user_role != 'staff' && $user_role != 'admin') {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

// Fetch all queries
$queries = [];
while ($row = $result->fetch_assoc()) {
    $queries[] = $row;
}

// Return queries as JSON
http_response_code(200);
echo json_encode(['status' => 'success', 'queries' => $queries]);

$stmt->close();
?>