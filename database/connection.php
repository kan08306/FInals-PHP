<?php
$database_host = 'localhost';
$database_username = 'root';
$database_password = '';
$database_name = 'shenanovents_db';

$conn = mysqli_connect($database_host, $database_username, $database_password, $database_name);

if (!$conn) {
    die('Database connection failed. Please check if MySQL is running and if the database name is correct.');
}

mysqli_set_charset($conn, 'utf8mb4');
