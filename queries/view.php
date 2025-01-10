<?php
// Set response type to JSON
header("Content-Type: application/json");

// Start session and check if user is logged in
require_once('../auth/isLoggedIn.php');

// Include database connection
require_once('../db/connect.php');

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Validate input
    if (!isset($_GET['id'])) {
        http_response_code(400); // Bad Request
        echo json_encode(['status' => 'error', 'message' => 'Query ID is required']);
        exit();
    }

    $query_id = $_GET['id'];

    // Get user role and ID from session
    $user_role = $_SESSION['user_role'];
    $user_id = $_SESSION['user_id'];

    // Prepare SQL query based on user role
    if ($user_role == 'staff' || $user_role == 'admin') {
        $sql = "SELECT queries.id, queries.subject, queries.message, users.name AS user_name 
                FROM queries 
                JOIN users ON queries.user_id = users.id 
                WHERE queries.id = ?";
    } else {
        $sql = "SELECT queries.id, queries.subject, queries.message, users.name AS user_name 
                FROM queries 
                JOIN users ON queries.user_id = users.id 
                WHERE queries.id = ? AND queries.user_id = ?";
    }

    // Prepare and execute statement
    $stmt = $conn->prepare($sql);

    if ($user_role == 'staff' || $user_role == 'admin') {
        $stmt->bind_param("i", $query_id);
    } else {
        $stmt->bind_param("ii", $query_id, $user_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Query not found']);
        $stmt->close();
        exit();
    }

    $query = $result->fetch_assoc();

    // Fetch related replies
    $reply_sql = "SELECT query_replies.id, query_replies.message, users.name AS user_name 
                  FROM query_replies 
                  JOIN users ON query_replies.user_id = users.id 
                  WHERE query_replies.query_id = ?";
    $reply_stmt = $conn->prepare($reply_sql);
    $reply_stmt->bind_param("i", $query_id);
    $reply_stmt->execute();
    $reply_result = $reply_stmt->get_result();

    $replies = [];
    while ($reply = $reply_result->fetch_assoc()) {
        $replies[] = $reply;
    }

    // Return query and replies as JSON
    http_response_code(200);
    echo json_encode(['status' => 'success', 'query' => $query, 'replies' => $replies]);

    $stmt->close();
    $reply_stmt->close();
} else {
    // Handle invalid request method
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>