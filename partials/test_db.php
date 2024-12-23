<?php
require 'config/constants.php';

// Koneksi ke database
$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
echo "Database connected successfully!";
?>