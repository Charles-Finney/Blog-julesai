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
// Note: In a real application, you might get the ID from the URL (e.g., /api/posts/123)
// and other data from the request body (e.g., JSON payload).
// Here, we are expecting all as form data for simplicity.
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
$title = isset($_POST['title']) ? trim($_POST['title']) : null;
$content = isset($_POST['content']) ? trim($_POST['content']) : null;
$type = isset($_POST['type']) ? trim($_POST['type']) : null;

// Localized content (optional for update)
$title_en = isset($_POST['title_en']) ? trim($_POST['title_en']) : null;
$content_en = isset($_POST['content_en']) ? trim($_POST['content_en']) : null;
$title_fr = isset($_POST['title_fr']) ? trim($_POST['title_fr']) : null;
$content_fr = isset($_POST['content_fr']) ? trim($_POST['content_fr']) : null;

// New optional fields for 'book' and 'video_tutorial'
$author = isset($_POST['author']) ? trim($_POST['author']) : null;
$cover_image_url = isset($_POST['cover_image_url']) ? trim($_POST['cover_image_url']) : null;
$publication_year = isset($_POST['publication_year']) ? filter_var($_POST['publication_year'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]) : null;
$isbn = isset($_POST['isbn']) ? trim($_POST['isbn']) : null;
$purchase_link = isset($_POST['purchase_link']) ? trim($_POST['purchase_link']) : null;
$video_url = isset($_POST['video_url']) ? trim($_POST['video_url']) : null;
$platform = isset($_POST['platform']) ? trim($_POST['platform']) : null;
$duration = isset($_POST['duration']) ? trim($_POST['duration']) : null;

// Validate input
if ($id === false || $id === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Valid Post ID is required.']);
    exit;
}

// Check if at least one field is provided for update.
if (empty($title) && empty($content) && empty($type) &&
    $title_en === null && $content_en === null && 
    $title_fr === null && $content_fr === null &&
    $author === null && $cover_image_url === null && $publication_year === null && $isbn === null && $purchase_link === null &&
    $video_url === null && $platform === null && $duration === null) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'At least one field must be provided for update.']);
    exit;
}

try {
    // Check if post exists
    $check_sql = "SELECT id FROM posts WHERE id = :id";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() == 0) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Post not found.']);
        exit;
    }

    // Build the update query dynamically based on provided fields
    $update_fields = [];
    $params_to_bind = ['id' => $id]; // Start with ID

    if ($title !== null) {
        $update_fields[] = "title = :title";
        $params_to_bind['title'] = $title;
    }
    if ($content !== null) {
        $update_fields[] = "content = :content";
        $params_to_bind['content'] = $content;
    }
    if ($type !== null) {
        $update_fields[] = "type = :type";
        $params_to_bind['type'] = $type;
    }
    // Localized fields - allow setting to NULL or empty string
    if ($title_en !== null) {
        $update_fields[] = "title_en = :title_en";
        $params_to_bind['title_en'] = $title_en;
    }
    if ($content_en !== null) {
        $update_fields[] = "content_en = :content_en";
        $params_to_bind['content_en'] = $content_en;
    }
    if ($title_fr !== null) {
        $update_fields[] = "title_fr = :title_fr";
        $params_to_bind['title_fr'] = $title_fr;
    }
    if ($content_fr !== null) {
        $update_fields[] = "content_fr = :content_fr";
        $params_to_bind['content_fr'] = $content_fr;
    }
    // New fields for book/video
    if ($author !== null) {
        $update_fields[] = "author = :author";
        $params_to_bind['author'] = $author;
    }
    if ($cover_image_url !== null) {
        $update_fields[] = "cover_image_url = :cover_image_url";
        $params_to_bind['cover_image_url'] = $cover_image_url;
    }
    if ($publication_year !== null) {
        $update_fields[] = "publication_year = :publication_year";
        $params_to_bind['publication_year'] = $publication_year;
    }
    if ($isbn !== null) {
        $update_fields[] = "isbn = :isbn";
        $params_to_bind['isbn'] = $isbn;
    }
    if ($purchase_link !== null) {
        $update_fields[] = "purchase_link = :purchase_link";
        $params_to_bind['purchase_link'] = $purchase_link;
    }
    if ($video_url !== null) {
        $update_fields[] = "video_url = :video_url";
        $params_to_bind['video_url'] = $video_url;
    }
    if ($platform !== null) {
        $update_fields[] = "platform = :platform";
        $params_to_bind['platform'] = $platform;
    }
    if ($duration !== null) {
        $update_fields[] = "duration = :duration";
        $params_to_bind['duration'] = $duration;
    }
    
    if (empty($update_fields)) {
        http_response_code(400); 
        echo json_encode(['status' => 'error', 'message' => 'No fields to update specified.']);
        exit;
    }

    $sql = "UPDATE posts SET " . implode(', ', $update_fields) . " WHERE id = :id";
    $stmt = $pdo->prepare($sql);

    // Bind parameters from the collected array
    foreach ($params_to_bind as $key => &$value) { 
        if ($key === 'id' || ($key === 'publication_year' && $value !== null)) { // publication_year can be int
            $stmt->bindParam(":$key", $value, PDO::PARAM_INT);
        } elseif ($key === 'publication_year' && $value === null) {
            $stmt->bindParam(":$key", $value, PDO::PARAM_NULL);
        }
         else {
            $stmt->bindParam(":$key", $value, PDO::PARAM_STR);
        }
    }
    unset($value); 

    // Execute statement
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            http_response_code(200); // OK
            echo json_encode(['status' => 'success', 'message' => 'Post updated successfully.']);
        } else {
            // This can happen if the submitted data is the same as existing data
            http_response_code(200); // OK (or 304 Not Modified, but 200 is simpler for client)
            echo json_encode(['status' => 'success', 'message' => 'No changes made to the post.']);
        }
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to update post.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
