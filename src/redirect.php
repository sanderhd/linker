<?php
require_once 'classes/Database.php';
require_once 'classes/Redirector.php';

$database = new Database();
$conn = $database->connect();

$redirector = new Redirector($conn);
$redirector->handleRequest();
?>