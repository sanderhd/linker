<?php
session_start();
include '../functions/db_connection.php';

header("Content-Type: application/json");

function generateShortId($conn, $length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    do {
        $shortId = '';
        for ($i = 0; $i < $length; $i++) {
            $shortId .= $characters[rand(0, strlen($characters) - 1)];
        }
        $stmt = $conn->prepare("SELECT id FROM links WHERE short_id = ?");
        $stmt->execute([$shortId]);
        $exists = $stmt->fetch();
    } while ($exists);
    return $shortId;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $stmt = $conn->prepare("SELECT * FROM links WHERE id=?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($data);
        } else {
            $stmt = $conn->prepare("SELECT * FROM links");
            $stmt->execute();
            $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($links);
        }
        break;

    case 'POST':
        $title = $input['title'];
        $url = $input['url'];
        $owner_id = $_SESSION['user_id'];
        $shortId = generateShortId($conn);
        $stmt = $conn->prepare("INSERT INTO links (title, url, owner_id, short_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $url, $owner_id, $shortId]);
        $linkId = $conn->lastInsertId();
        echo json_encode(["message" => "Link added successfully", "short_id" => $shortId, "id" => $linkId]);
        break;

    case 'PUT':
        $id = $_GET['id'];
        $title = $input['title'];
        $url = $input['url'];
        $owner_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("UPDATE links SET title=?, url=?, owner_id=? WHERE id=?");
        $stmt->execute([$title, $url, $owner_id, $id]);
        echo json_encode(["message" => "Link updated successfully"]);
        break;

    case 'DELETE':
        $id = $_GET['id'];
        $stmt = $conn->prepare("DELETE FROM links WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Link deleted successfully"]);
        break;

    default:
        echo json_encode(["message" => "Invalid request method"]);
        break;
}
