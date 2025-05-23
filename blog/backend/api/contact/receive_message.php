<?php
header('Content-Type: application/json');
// Adjust the path to db_connect.php: from contact/ to backend/
require_once __DIR__ . '/../../db_connect.php'; 

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use POST.']);
    exit;
}

// Get POST data
$name = isset($_POST['name']) ? trim($_POST['name']) : null;
$email = isset($_POST['email']) ? trim($_POST['email']) : null; // Optional
$message = isset($_POST['message']) ? trim($_POST['message']) : null;

// Basic validation
if (empty($name) || empty($message)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Name and message are required.']);
    exit;
}

// Validate email format if provided
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

try {
    // Prepare SQL statement
    $sql = "INSERT INTO messages (name, email, message) VALUES (:name, :email, :message)";
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR); // email can be null
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);

    // Execute statement
    if ($stmt->execute()) {
        $messageId = $pdo->lastInsertId();
        http_response_code(201); // Created
        echo json_encode([
            'status' => 'success',
            'message' => 'Message received successfully.',
            'message_id' => $messageId
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['status' => 'error', 'message' => 'Failed to save message.']);
    }
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    // Log error to a file or monitoring system in a real application
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
