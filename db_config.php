<?php
// Database Configuration
define('DB_SERVER', 'localhost'); // Or your DB host
define('DB_USERNAME', 'root');    // Your DB username
define('DB_PASSWORD', '');        // Your DB password
define('DB_NAME', 'tap_swap_db'); // Your DB name

// Attempt to connect to MySQL database
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if($conn === false){
    // Log error instead of displaying to user in production
    error_log("Database connection failed: " . mysqli_connect_error());
    // Send a generic error response if this were an API context, or display a user-friendly error page
    die("ERROR: Could not connect to the database. Please try again later.");
}

// Set character set to utf8mb4 for better compatibility
mysqli_set_charset($conn, "utf8mb4");

// Optional: Set timezone (align with your server/application timezone)
// date_default_timezone_set('Asia/Kolkata');
?>