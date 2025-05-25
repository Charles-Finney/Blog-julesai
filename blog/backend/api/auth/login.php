<?php
// Start PHP session
session_start();

header('Content-Type: application/json');
// Adjust the path to db_connect.php: from auth/ to backend/
require_once __DIR__ . '/../../db_connect.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use POST.']);
    exit;
}

// Get POST data
$identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null; // Password not trimmed

// --- Validation ---
if (empty($identifier) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username/email and password are required.']);
    exit;
}

try {
    // --- Fetch User from Database ---
    $sql = "SELECT id, username, email, password_hash, name, profile_picture_url, role_id 
            FROM users 
            WHERE username = :identifier OR email = :identifier 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
        exit;
    }

    // --- Verify Password ---
    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401); // Unauthorized
        echo json_encode(['status' => 'error', 'message' => 'Invalid credentials.']);
        exit;
    }

    // --- Session Management (On Successful Login) ---
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role_id'] = $user['role_id'];
    
    // Optionally, fetch and store role_name
    $role_sql = "SELECT role_name FROM roles WHERE id = :role_id LIMIT 1";
    $role_stmt = $pdo->prepare($role_sql);
    $role_stmt->bindParam(':role_id', $user['role_id'], PDO::PARAM_INT);
    $role_stmt->execute();
    $role_data = $role_stmt->fetch(PDO::FETCH_ASSOC);
    if ($role_data) {
        $_SESSION['role_name'] = $role_data['role_name'];
    }


    // --- Response ---
    http_response_code(200); // OK
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful.',
        'user_data' => [
            'user_id' => (int)$user['id'], // Cast to int for consistency
            'username' => $user['username'],
            'email' => $user['email'],
            'name' => $user['name'],
            'profile_picture_url' => $user['profile_picture_url'],
            'role_id' => (int)$user['role_id'], // Cast to int
            'role_name' => $role_data ? $role_data['role_name'] : null // Include role_name if fetched
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    // Log error: error_log("Database error during login: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
