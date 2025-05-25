<?php
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
$username = isset($_POST['username']) ? trim($_POST['username']) : null;
$email = isset($_POST['email']) ? trim($_POST['email']) : null;
$password = isset($_POST['password']) ? $_POST['password'] : null; // Password not trimmed to allow spaces if intended
$name = isset($_POST['name']) ? trim($_POST['name']) : null; // Optional
$profile_picture_url = isset($_POST['profile_picture_url']) ? trim($_POST['profile_picture_url']) : null; // Optional

// --- Validation ---
// 1. Required fields
if (empty($username) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Username, email, and password are required.']);
    exit;
}

// 2. Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

// 3. Validate password strength (min 8 characters)
if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password must be at least 8 characters long.']);
    exit;
}

try {
    // --- Check for Existing User ---
    $check_sql = "SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $check_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $check_stmt->execute();
    
    $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        $conflicting_field = ($existing_user['username'] === $username) ? 'Username' : 'Email';
        http_response_code(409); // Conflict
        echo json_encode(['status' => 'error', 'message' => $conflicting_field . ' already exists.']);
        exit;
    }

    // --- Password Hashing ---
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    if ($password_hash === false) {
        // password_hash can return false on error
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to hash password.']);
        exit;
    }
    
    // --- Determine Role ID for 'user' ---
    $role_sql = "SELECT id FROM roles WHERE role_name = 'user' LIMIT 1";
    $role_stmt = $pdo->query($role_sql); // Simple query, no user input
    $user_role = $role_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_role || !isset($user_role['id'])) {
        // This indicates a setup issue (default 'user' role missing)
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Default user role not found. Please contact administrator.']);
        exit;
    }
    $role_id = $user_role['id'];

    // --- Database Insertion ---
    $insert_sql = "INSERT INTO users (username, email, password_hash, name, profile_picture_url, role_id) 
                   VALUES (:username, :email, :password_hash, :name, :profile_picture_url, :role_id)";
    $insert_stmt = $pdo->prepare($insert_sql);
    
    $insert_stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $insert_stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $insert_stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
    $insert_stmt->bindParam(':name', $name, PDO::PARAM_STR); // Will bind NULL if $name is null
    $insert_stmt->bindParam(':profile_picture_url', $profile_picture_url, PDO::PARAM_STR); // Will bind NULL if $profile_picture_url is null
    $insert_stmt->bindParam(':role_id', $role_id, PDO::PARAM_INT);

    if ($insert_stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to register user.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    // Log error to a file or monitoring system in a real application
    // For example: error_log("Database error during registration: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
