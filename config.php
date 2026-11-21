<?php
// Database connection. Change user/password if your MySQL is different.
$DB_HOST = 'localhost';
$DB_NAME = 'dss';
$DB_USER = 'root';
$DB_PASS = '';   // XAMPP/WAMP default is empty

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('Database connection failed: ' . htmlspecialchars($ex->getMessage()) .
        '<br>Did you import database.sql and start MySQL?');
}

// Safe output helper
function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
