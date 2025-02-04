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
  die("Connection failed: ".$e->getMessage());
}
if(!isset($_SESSION['user_id'])){
  exit();
}
$favorite_subjects = isset($_POST['favorite_subjects']) ? trim($_POST['favorite_subjects']) : '';
$daily_study_time = isset($_POST['daily_study_time']) ? trim($_POST['daily_study_time']) : '';
$grade = isset($_POST['grade']) ? trim($_POST['grade']) : '';
if($favorite_subjects !== '' && $daily_study_time !== '' && $grade !== ''){
  $survey_completed = 1;
} else {
  $survey_completed = 0;
}
$stmt = $pdo->prepare("UPDATE utilizatori SET favorite_subjects = ?, daily_study_time = ?, grade = ?, survey_completed = ? WHERE id = ?");
$stmt->execute([$favorite_subjects, $daily_study_time, $grade, $survey_completed, $_SESSION['user_id']]);
?>
