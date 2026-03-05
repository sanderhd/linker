<?php
session_start();
require_once '../functions/db_connection.php';

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
