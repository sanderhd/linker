<?php
session_start();
require_once '../classes/Database.php';

// database connecten
$database = new Database();
$conn = $database->connect();

// controleren of admin is
if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
	header("Location: ../login.php");
	exit();
}

// id uit url halen
$id = $_GET['id'] ?? null;

// naar index sturen als er geen id is
if (!$id) {
	header("Location: index.php");
	exit();
}

// deleten
$stmt = $conn->prepare("DELETE FROM links WHERE id = ?");
$stmt->execute([$id]);

// terug naar index
header("Location: index.php");
exit();
?>
