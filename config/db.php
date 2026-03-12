<?php

require_once __DIR__ . '/app.php';

$host = 'localhost';
$db_name = 'rsvp_master';
$db_user = 'root';
$db_pass = '';

$dsn = "mysql:host=$host;dbname=$db_name;charset=utf8mb4";

try {
	$pdo = new PDO($dsn, $db_user, $db_pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	die('Database connection failed: ' . $e->getMessage());
}

