<?php
header('Content-Type: application/json');
// Adjust the path to db_connect.php: from counter/ to backend/
require_once __DIR__ . '/../../db_connect.php';

$page_identifier = null;

// Get page_identifier from POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_identifier = isset($_POST['page_identifier']) ? trim($_POST['page_identifier']) : null;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page_identifier = isset($_GET['page_identifier']) ? trim($_GET['page_identifier']) : null;
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

if (empty($page_identifier)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Page identifier is required.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Increment counter
        
        // Check if identifier exists
        $select_sql = "SELECT id, count FROM click_counts WHERE page_identifier = :page_identifier";
        $select_stmt = $pdo->prepare($select_sql);
        $select_stmt->bindParam(':page_identifier', $page_identifier, PDO::PARAM_STR);
        $select_stmt->execute();
        $row = $select_stmt->fetch(PDO::FETCH_ASSOC);

        $new_count = 0;
        if ($row) {
            // Identifier exists, increment count
            $new_count = $row['count'] + 1;
            $update_sql = "UPDATE click_counts SET count = :count WHERE id = :id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':count', $new_count, PDO::PARAM_INT);
            $update_stmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
            $update_stmt->execute();
        } else {
            // Identifier does not exist, insert new row
            $new_count = 1;
            $insert_sql = "INSERT INTO click_counts (page_identifier, count) VALUES (:page_identifier, :count)";
            $insert_stmt = $pdo->prepare($insert_sql);
            $insert_stmt->bindParam(':page_identifier', $page_identifier, PDO::PARAM_STR);
            $insert_stmt->bindParam(':count', $new_count, PDO::PARAM_INT);
            $insert_stmt->execute();
        }
        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'page_identifier' => $page_identifier, 'count' => $new_count]);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Retrieve counter
        $sql = "SELECT count FROM click_counts WHERE page_identifier = :page_identifier";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':page_identifier', $page_identifier, PDO::PARAM_STR);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = 0;
        if ($result) {
            $count = (int)$result['count'];
        }
        
        http_response_code(200); // OK
        echo json_encode(['status' => 'success', 'page_identifier' => $page_identifier, 'count' => $count]);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Log error to a file or monitoring system in a real application
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
