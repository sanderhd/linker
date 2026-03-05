<?php

require_once '../functions/db_connection.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("DELETE FROM links WHERE id = ? AND owner_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);

header('Location: index.php');
exit;
?>