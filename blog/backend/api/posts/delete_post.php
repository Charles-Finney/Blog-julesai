<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../db_connect.php'; // Adjust path to db_connect.php

// Determine request method and get ID
$id = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method or ID not provided. Please use GET or POST with an "id" parameter.']);
    exit;
}

// Validate ID
if ($id === false || $id === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Valid Post ID is required.']);
    exit;
}

try {
    // Check if post exists before trying to delete
    $check_sql = "SELECT id FROM posts WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Post not found.']);
        exit;
    }

    // Prepare SQL statement for deletion
    $sql = "DELETE FROM posts WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Execute statement
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200); // OK
            echo json_encode(['status' => 'success', 'message' => 'Post deleted successfully.']);
        } else {
            // This case should ideally not be reached if the check above works,
            // but as a fallback.
            http_response_code(404); // Not Found (already deleted or never existed)
            echo json_encode(['status' => 'error', 'message' => 'Post not found or already deleted.']);
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete post.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
