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

// New fields for 'book' and 'video_tutorial' types (all optional)
$author = isset($_POST['author']) ? trim($_POST['author']) : null;
$cover_image_url = isset($_POST['cover_image_url']) ? trim($_POST['cover_image_url']) : null;
$publication_year = isset($_POST['publication_year']) ? filter_var($_POST['publication_year'], FILTER_VALIDATE_INT, ['options' => ['default' => null]]) : null;
$isbn = isset($_POST['isbn']) ? trim($_POST['isbn']) : null;
$purchase_link = isset($_POST['purchase_link']) ? trim($_POST['purchase_link']) : null;
$video_url = isset($_POST['video_url']) ? trim($_POST['video_url']) : null;
$platform = isset($_POST['platform']) ? trim($_POST['platform']) : null;
$duration = isset($_POST['duration']) ? trim($_POST['duration']) : null;


// Validate input: default title and content are still required
if (empty($title) || empty($content)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Default title and content are required.']);
    exit;
}

try {
    // Prepare SQL statement
    $sql = "INSERT INTO posts (title, content, type, title_en, content_en, title_fr, content_fr,
                               author, cover_image_url, publication_year, isbn, purchase_link,
                               video_url, platform, duration) 
            VALUES (:title, :content, :type, :title_en, :content_en, :title_fr, :content_fr,
                    :author, :cover_image_url, :publication_year, :isbn, :purchase_link,
                    :video_url, :platform, :duration)";
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':title', $title, PDO::PARAM_STR);
    $stmt->bindParam(':content', $content, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    $stmt->bindParam(':title_en', $title_en, PDO::PARAM_STR);
    $stmt->bindParam(':content_en', $content_en, PDO::PARAM_STR);
    $stmt->bindParam(':title_fr', $title_fr, PDO::PARAM_STR);
    $stmt->bindParam(':content_fr', $content_fr, PDO::PARAM_STR);
    
    // Bind new optional parameters
    $stmt->bindParam(':author', $author, PDO::PARAM_STR);
    $stmt->bindParam(':cover_image_url', $cover_image_url, PDO::PARAM_STR);
    $stmt->bindParam(':publication_year', $publication_year, $publication_year === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(':isbn', $isbn, PDO::PARAM_STR);
    $stmt->bindParam(':purchase_link', $purchase_link, PDO::PARAM_STR);
    $stmt->bindParam(':video_url', $video_url, PDO::PARAM_STR);
    $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);
    $stmt->bindParam(':duration', $duration, PDO::PARAM_STR);

    // Start transaction
    $pdo->beginTransaction();

    // Execute statement for post insertion
    if ($stmt->execute()) {
        $last_post_id = $pdo->lastInsertId();
        $uploaded_image_paths = [];

        // Handle image uploads if type is 'software'
        if ($type === 'software' && isset($_FILES['project_images'])) {
            $image_files = $_FILES['project_images'];
            $image_descriptions = isset($_POST['image_descriptions']) ? $_POST['image_descriptions'] : [];
            $image_descriptions_en = isset($_POST['image_descriptions_en']) ? $_POST['image_descriptions_en'] : [];
            $image_descriptions_fr = isset($_POST['image_descriptions_fr']) ? $_POST['image_descriptions_fr'] : [];
            
            $upload_dir = __DIR__ . '/../../../assets/project_images/'; // Relative to current script's dir
            if (!is_dir($upload_dir)) {
                // This should ideally be created by setup/deployment scripts
                // For the sandbox, we might not have permission to create it outside /app
                // mkdir($upload_dir, 0775, true); // Attempt to create if not exists
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            $max_files = 10;

            if (count($image_files['name']) > $max_files) {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => "Cannot upload more than $max_files images."]);
                exit;
            }

            for ($i = 0; $i < count($image_files['name']); $i++) {
                if ($image_files['error'][$i] === UPLOAD_ERR_OK) {
                    // Validate type
                    if (!in_array($image_files['type'][$i], $allowed_types)) {
                        $pdo->rollBack();
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => "Invalid file type for image " . ($i+1) . ". Only JPEG, PNG, GIF allowed."]);
                        exit;
                    }
                    // Validate size
                    if ($image_files['size'][$i] > $max_size) {
                        $pdo->rollBack();
                        http_response_code(400);
                        echo json_encode(['status' => 'error', 'message' => "Image " . ($i+1) . " exceeds max size of 5MB."]);
                        exit;
                    }

                    $file_extension = pathinfo($image_files['name'][$i], PATHINFO_EXTENSION);
                    $unique_filename = uniqid('postimg_' . $last_post_id . '_', true) . '.' . $file_extension;
                    $destination = $upload_dir . $unique_filename;

                    if (move_uploaded_file($image_files['tmp_name'][$i], $destination)) {
                        $image_url = 'assets/project_images/' . $unique_filename; // Relative path for DB
                        $uploaded_image_paths[] = $image_url;

                        $img_sql = "INSERT INTO project_images (post_id, image_url, description, description_en, description_fr, sort_order) 
                                    VALUES (:post_id, :image_url, :description, :description_en, :description_fr, :sort_order)";
                        $img_stmt = $pdo->prepare($img_sql);
                        
                        $desc = isset($image_descriptions[$i]) ? trim($image_descriptions[$i]) : null;
                        $desc_en = isset($image_descriptions_en[$i]) ? trim($image_descriptions_en[$i]) : null;
                        $desc_fr = isset($image_descriptions_fr[$i]) ? trim($image_descriptions_fr[$i]) : null;
                        
                        $img_stmt->bindParam(':post_id', $last_post_id, PDO::PARAM_INT);
                        $img_stmt->bindParam(':image_url', $image_url, PDO::PARAM_STR);
                        $img_stmt->bindParam(':description', $desc, PDO::PARAM_STR);
                        $img_stmt->bindParam(':description_en', $desc_en, PDO::PARAM_STR);
                        $img_stmt->bindParam(':description_fr', $desc_fr, PDO::PARAM_STR);
                        $img_stmt->bindParam(':sort_order', $i, PDO::PARAM_INT);
                        
                        if (!$img_stmt->execute()) {
                            $pdo->rollBack();
                            // Attempt to delete already uploaded files for this post
                            foreach($uploaded_image_paths as $path) { if(file_exists($upload_dir . basename($path))) unlink($upload_dir . basename($path));}
                            http_response_code(500);
                            echo json_encode(['status' => 'error', 'message' => 'Failed to save image metadata for image ' . ($i+1)]);
                            exit;
                        }
                    } else {
                        $pdo->rollBack();
                        foreach($uploaded_image_paths as $path) { if(file_exists($upload_dir . basename($path))) unlink($upload_dir . basename($path));}
                        http_response_code(500);
                        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded image ' . ($i+1)]);
                        exit;
                    }
                } elseif ($image_files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                    // Handle other upload errors
                    $pdo->rollBack();
                    http_response_code(400);
                    echo json_encode(['status' => 'error', 'message' => 'Error uploading image ' . ($i+1) . '. Error code: ' . $image_files['error'][$i]]);
                    exit;
                }
            }
        }

        $pdo->commit();
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'Post and associated images (if any) created successfully.',
            'post_id' => $last_post_id,
            'image_urls' => $uploaded_image_paths
        ]);

    } else {
        $pdo->rollBack(); // Rollback if post insertion fails
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to create post.']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Attempt to delete any orphaned uploaded files if error occurs after some uploads
    if (!empty($uploaded_image_paths) && isset($upload_dir)) {
         foreach($uploaded_image_paths as $path) { if(file_exists($upload_dir . basename($path))) unlink($upload_dir . basename($path));}
    }
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
