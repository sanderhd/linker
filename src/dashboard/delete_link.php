<?php

// database connecten
require_once '../classes/Database.php';
$database = new Database();
$conn = $database->connect();
session_start();

$id = $_GET['id'] ?? null;

// check of id
if (!$id) {
    header('Location: index.php');
    exit;
}

// delete link alleen als hij de owner is
$stmt = $conn->prepare("DELETE FROM links WHERE id = ? AND owner_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

header('Location: index.php');
exit;
?>