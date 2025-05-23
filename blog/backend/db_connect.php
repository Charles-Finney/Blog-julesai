<?php
// Database configuration
define('DB_HOST', 'localhost'); // Or your database host
define('DB_USER', 'your_db_user');    // Your database username
define('DB_PASS', 'your_db_password');  // Your database password
define('DB_NAME', 'blog_db');      // Your database name

// Attempt to connect to the database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // You can remove the echo statement in production
    // echo "Connected successfully to " . DB_NAME;
} catch(PDOException $e) {
    // Handle connection error
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit; // Stop script execution if connection fails
}
?>
