<?php
$servername = "57.129.44.247:3306";
$username = "u28_Er7tNIyIDl";
$password = "p5.ESZ9bCIWveVpz7mUage1^";
$dbname = "s28_linker";

try {
  $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
} catch(PDOException $e) {
  echo "<script>console.log('Connection failed: " . $e->getMessage() . "');</script>";
}
?>