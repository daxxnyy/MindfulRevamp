<?php
session_start();
$host = 'localhost';
$dbname = 'inregistrare_moderna';
$user = 'root';
$pass = '';
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection failed: ".$e->getMessage()); }
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $nume = isset($_POST['nume']) ? trim($_POST['nume']) : '';
  $parola = isset($_POST['parola']) ? $_POST['parola'] : '';
  if(empty($nume) || empty($parola)){
    $error = "Toate câmpurile sunt necesare.";
  } else {
    $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE nume = ?");
    $stmt->execute([$nume]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if($userData && password_verify($parola, $userData['parola'])){
      $_SESSION['user_id'] = $userData['id'];
      $_SESSION['nume'] = $userData['nume'];
      header("Location: principala.html");
      exit();
    } else {
      $error = "Parola sau numele sunt gresite. <a href='uitatparola.html' style='color:inherit; text-decoration:underline;'>Ai uitat parola?</a>";
    }
  }
} else {
  header("Location: signin.html");
  exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mindful: Eroare Autentificare</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root { --primary: #444; --bg: #ececec; --light-bg: #fff; --text: #fff; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Roboto', sans-serif; background-color: var(--bg); display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    .error-box { background: red; padding: 30px; border-radius: 8px; text-align: center; }
    .error-box a { color: var(--light-bg); text-decoration: underline; }
    .error-box h2 { margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="error-box">
    <h2>Eroare Autentificare</h2>
    <p><?php echo $error; ?></p>
    <p><a href="signin.html" style="color:#fff; text-decoration:underline;">Înapoi la autentificare</a></p>
  </div>
</body>
</html>
