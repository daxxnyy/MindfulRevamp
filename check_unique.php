<?php
session_start();
header('Content-Type: application/json');
$host='localhost';
$dbname='inregistrare_moderna';
$user='root';
$pass='';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
  echo json_encode(["nameTaken" => false, "phoneTaken" => false]);
  exit();
}
$data = json_decode(file_get_contents("php://input"), true);
$nume = isset($data['nume']) ? trim($data['nume']) : '';
$telefon = isset($data['telefon']) ? trim($data['telefon']) : '';
$nameTaken = false;
$phoneTaken = false;
if(!empty($nume)) {
  $stmt = $pdo->prepare("SELECT id FROM utilizatori WHERE nume = ?");
  $stmt->execute([$nume]);
  if($stmt->fetch()) {
    $nameTaken = true;
  }
}
if(!empty($telefon)) {
  $stmt = $pdo->prepare("SELECT id FROM utilizatori WHERE telefon = ?");
  $stmt->execute([$telefon]);
  if($stmt->fetch()) {
    $phoneTaken = true;
  }
}
echo json_encode(["nameTaken" => $nameTaken, "phoneTaken" => $phoneTaken]);
?>
