<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../db_connect.php'; // Adjust path to db_connect.php

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use POST.']);
    exit;
}

// Get POST data
$title = isset($_POST['title']) ? trim($_POST['title']) : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : null;
$type = isset($_POST['type']) ? trim($_POST['type']) : null; // Optional type

// Localized content (optional)
$title_en = isset($_POST['title_en']) ? trim($_POST['title_en']) : null;
$content_en = isset($_POST['content_en']) ? trim($_POST['content_en']) : null;
$title_fr = isset($_POST['title_fr']) ? trim($_POST['title_fr']) : null;
$content_fr = isset($_POST['content_fr']) ? trim($_POST['content_fr']) : null;

// Validate input: default title and content are still required
if (empty($title) || empty($content)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Default title and content are required.']);
    exit;
}

try {
    // Prepare SQL statement
    $sql = "INSERT INTO posts (title, content, type, title_en, content_en, title_fr, content_fr) 
            VALUES (:title, :content, :type, :title_en, :content_en, :title_fr, :content_fr)";
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':title_en', $title_en, PDO::PARAM_STR);
    $stmt->bindParam(':content_en', $content_en, PDO::PARAM_STR);
    $stmt->bindParam(':title_fr', $title_fr, PDO::PARAM_STR);
    $stmt->bindParam(':content_fr', $content_fr, PDO::PARAM_STR);

    // Execute statement
    if ($stmt->execute()) {
        $postId = $pdo->lastInsertId();
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'Post created successfully.',
            'post_id' => $postId
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to create post.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Log error to a file or monitoring system in a real application
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
