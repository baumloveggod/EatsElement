<?php
// Include your configuration file
require_once __DIR__ . '/../../config/config.php';


// Create a MySQL database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use this $conn object in your other scripts to interact with the database
?>
