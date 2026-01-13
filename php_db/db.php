<?php
// php_db/db.php

declare(strict_types=1);

$DB_HOST = "sql100.byethost22.com";
$DB_NAME = "b22_40780161_the_flavor_forge";
$DB_USER = "b22_40780161";
$DB_PASS = "Moradeldin2!";

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed.");
}
