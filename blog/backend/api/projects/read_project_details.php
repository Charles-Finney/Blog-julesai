<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../db_connect.php'; // Adjust path to db_connect.php

// Get post_id and lang from GET parameters
$post_id = isset($_GET['post_id']) ? filter_var($_GET['post_id'], FILTER_VALIDATE_INT) : null;
$lang = isset($_GET['lang']) ? strtolower(trim($_GET['lang'])) : null;

if (!$post_id) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Valid Post ID is required.']);
    exit;
}

try {
    // Fetch main post data
    $post_sql = "SELECT id, title, content, title_en, content_en, title_fr, content_fr, 
                        type, created_at, updated_at 
                 FROM posts 
                 WHERE id = :post_id AND type = 'software'";
    
    $post_stmt = $pdo->prepare($post_sql);
    $post_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $post_stmt->execute();
    
    $post_data = $post_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post_data) {
        http_response_code(404); // Not Found
        echo json_encode(['status' => 'error', 'message' => 'Project not found or is not of type "software".']);
        exit;
    }

    // Determine localized title and content for the main post
    $localized_title = $post_data['title']; // Default
    $localized_content = $post_data['content']; // Default

    if ($lang === 'en') {
        $localized_title = !empty($post_data['title_en']) ? $post_data['title_en'] : $post_data['title'];
        $localized_content = !empty($post_data['content_en']) ? $post_data['content_en'] : $post_data['content'];
    } elseif ($lang === 'fr') {
        $localized_title = !empty($post_data['title_fr']) ? $post_data['title_fr'] : $post_data['title'];
        $localized_content = !empty($post_data['content_fr']) ? $post_data['content_fr'] : $post_data['content'];
    }

    $project_details = [
        'id' => $post_data['id'],
        'localized_title' => $localized_title,
        'localized_content' => $localized_content,
        'type' => $post_data['type'],
        'created_at' => $post_data['created_at'],
        'updated_at' => $post_data['updated_at'],
        // Optionally include default or all language versions of main post content
        // 'title_default' => $post_data['title'],
        // 'content_default' => $post_data['content'],
        // 'title_en' => $post_data['title_en'], 'content_en' => $post_data['content_en'],
        // 'title_fr' => $post_data['title_fr'], 'content_fr' => $post_data['content_fr'],
        'images' => [] // Initialize images array
    ];

    // Fetch associated images
    $images_sql = "SELECT id, image_url, description, description_en, description_fr, sort_order 
                   FROM project_images 
                   WHERE post_id = :post_id 
                   ORDER BY sort_order ASC";
    
    $images_stmt = $pdo->prepare($images_sql);
    $images_stmt->bindParam(':post_id', $post_id, PDO::PARAM_INT);
    $images_stmt->execute();
    
    $images_data = $images_stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($images_data) {
        foreach ($images_data as $image) {
            // Determine localized description for each image
            $localized_desc = $image['description']; // Default
            if ($lang === 'en') {
                $localized_desc = !empty($image['description_en']) ? $image['description_en'] : $image['description'];
            } elseif ($lang === 'fr') {
                $localized_desc = !empty($image['description_fr']) ? $image['description_fr'] : $image['description'];
            }

            $project_details['images'][] = [
                'id' => $image['id'],
                'image_url' => $image['image_url'],
                'localized_description' => $localized_desc,
                'sort_order' => $image['sort_order'],
                // Optionally include default or all language versions of description
                // 'description_default' => $image['description'],
                // 'description_en' => $image['description_en'],
                // 'description_fr' => $image['description_fr']
            ];
        }
    }

    http_response_code(200); // OK
    echo json_encode(['status' => 'success', 'data' => $project_details]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
