<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../db_connect.php'; // Adjust path to db_connect.php

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use GET.']);
    exit;
}

// Check for type filter
$type_filter = isset($_GET['type']) ? trim($_GET['type']) : null;

// Check for language filter
$lang = isset($_GET['lang']) ? strtolower(trim($_GET['lang'])) : null;

try {
    // Base SQL query - fetch all language versions and common fields, plus new type-specific fields
    $sql = "SELECT id, title, content, title_en, content_en, title_fr, content_fr, type, 
                   author, cover_image_url, publication_year, isbn, purchase_link,
                   video_url, platform, duration,
                   created_at, updated_at 
            FROM posts";

    if ($type_filter) {
        $sql .= " WHERE type = :type";
    }

    $sql .= " ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);

    if ($type_filter) {
        $stmt->bindParam(':type', $type_filter, PDO::PARAM_STR);
    }

    // Execute statement
    $stmt->execute();

    // Fetch all posts
    $raw_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $processed_posts = [];

    if ($raw_posts) {
        foreach ($raw_posts as $post) {
            $localized_title = $post['title']; // Default
            $localized_content = $post['content']; // Default

            if ($lang === 'en') {
                $localized_title = !empty($post['title_en']) ? $post['title_en'] : $post['title'];
                $localized_content = !empty($post['content_en']) ? $post['content_en'] : $post['content'];
            } elseif ($lang === 'fr') {
                $localized_title = !empty($post['title_fr']) ? $post['title_fr'] : $post['title'];
                $localized_content = !empty($post['content_fr']) ? $post['content_fr'] : $post['content'];
            }
            
            $current_post_data = [
                'id' => $post['id'],
                'localized_title' => $localized_title,
                'localized_content' => $localized_content,
                'type' => $post['type'],
                'created_at' => $post['created_at'],
                'updated_at' => $post['updated_at'],
                // Include new fields, they will be null if not applicable to type or not set
                'author' => $post['author'],
                'cover_image_url' => $post['cover_image_url'],
                'publication_year' => $post['publication_year'] === null ? null : (int)$post['publication_year'],
                'isbn' => $post['isbn'],
                'purchase_link' => $post['purchase_link'],
                'video_url' => $post['video_url'],
                'platform' => $post['platform'],
                'duration' => $post['duration'],
            ];
            
            // Optionally, to keep the payload smaller, only include type-specific fields 
            // if the type matches. However, including them as null is also fine.
            // Example:
            // if ($post['type'] !== 'book') {
            //     unset($current_post_data['author'], $current_post_data['cover_image_url'], ...);
            // }
            // if ($post['type'] !== 'video_tutorial') {
            //     unset($current_post_data['video_url'], $current_post_data['platform'], ...);
            // }

            $processed_posts[] = $current_post_data;
        }
        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'data' => $processed_posts]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'success', 'message' => 'No posts found.', 'data' => []]);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Log error to a file or monitoring system in a real application
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
