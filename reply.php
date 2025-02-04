<?php
session_start();
$host = 'localhost';
$dbname = 'inregistrare_moderna';
$user = 'root';
$pass = '';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die(); }
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['parent_id']) && isset($_POST['reply_text'])){
  $parent_id = (int) $_POST['parent_id'];
  $reply_text = trim($_POST['reply_text']);
  if($reply_text !== ''){
    $stmt = $pdo->prepare("INSERT INTO comentarii (nume, comentariu, parent_id) VALUES ('Dan', :comentariu, :parent_id)");
    $stmt->execute([':comentariu' => $reply_text, ':parent_id' => $parent_id]);
  }
  header("Location: lectie1.php");
  exit();
}
?>
