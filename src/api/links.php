<?php

session_start();
header("Content-Type: application/json");

require_once "../classes/Database.php";
require_once "../classes/Link.php";
require_once "../classes/Validator.php";

$database = new Database();
$db = $database->connect();

$link = new Link($db);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

switch ($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            echo json_encode($link->getById($_GET['id']));
        } else {
            echo json_encode($link->getAll());
        }

        break;

    case 'POST':
        $shortId = $link->generateShortId();

        $title = trim($input['title'] ?? '');
        $url = trim($input['url'] ?? '');

        if (!Validator::required($title) || !Validator::required($url)) {
            http_response_code(400);
            echo json_encode(["message" => "Title and URL are required."]);
            break;
        }

        if (!Validator::isUrl($url)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid URL."]);
            break;
        }

        $link->create(
            $title,
            $url,
            $_SESSION['user_id'],
            $shortId
        );

        echo json_encode([
            "message" => "Link added",
            "short_id" => $shortId
        ]);

        break;

    case 'PUT':
        $link->update(
            $_GET['id'],
            $input['title'],
            $input['url'],
            $_SESSION['user_id']
        );

        echo json_encode(["message" => "Updated"]);

        break;

    case 'DELETE':
        $link->delete($_GET['id']);

        echo json_encode(["message" => "Deleted"]);

        break;
}