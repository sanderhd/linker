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

// id uit de url pakken
$id = $_GET['id'] ?? null;

// als er geen id is terug naar overzicht
if (!$id) {
	header("Location: users.php");
	exit();
}

// voorkomen dat admin zichzelf verwijderd
if ($id == $_SESSION['user_id']) {
	header("Location: users.php");
	exit();
}

// deleten
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header("Location: users.php");
exit();
?>
