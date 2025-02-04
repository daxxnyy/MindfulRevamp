<?php
session_start();
$host = 'localhost';
$dbname = 'inregistrare_moderna';
$user = 'root';
$pass = '';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo json_encode(["completed" => false]);
  exit();
}
if(!isset($_SESSION['user_id'])){
  echo json_encode(["completed" => false]);
  exit();
}
$stmt = $pdo->prepare("SELECT survey_completed FROM utilizatori WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if($row && $row['survey_completed'] == 1){
  echo json_encode(["completed" => true]);
} else {
  echo json_encode(["completed" => false]);
}
?>
