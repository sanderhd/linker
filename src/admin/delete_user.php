<?php
session_start();
require_once '../classes/Database.php';

$database = new Database();
$conn = $database->connect();

if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	header("Location: ../login.php");
	exit();
}

$id = $_GET['id'] ?? null;

if (!$id) {
	header("Location: users.php");
	exit();
}

if ($id == $_SESSION['user_id']) {
	header("Location: users.php");
	exit();
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header("Location: users.php");
exit();
?>
