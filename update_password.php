<?php
$host = 'localhost';
$dbname = 'inregistrare_moderna';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Conexiunea la baza de date a eșuat: " . $e->getMessage());
}

$message = '';
$messageType = '';
$tokenForLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $tokenForLink = $token;
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    if ($password !== $password_confirm) {
        $message = "Parolele nu se potrivesc.";
        $messageType = "error";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE reset_token = ?");
        $stmt->execute([$token]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            $currentDateTime = date("Y-m-d H:i:s");
            if ($currentDateTime > $userData['reset_token_expiration']) {
                $message = "Link-ul de resetare a parolei a expirat.";
                $messageType = "error";
            } else {
                $newPasswordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE utilizatori SET parola = ?, reset_token = NULL, reset_token_expiration = NULL WHERE id = ?");
                $stmt->execute([$newPasswordHash, $userData['id']]);
                $message = "Parola a fost resetată cu succes!";
                $messageType = "success";
            }
        } else {
            $message = "Token invalid.";
            $messageType = "error";
        }
    }
} else {
    header("Location: uitatparola.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <?php if ($messageType === "success"): ?>
  <meta http-equiv="refresh" content="3;url=signin.html">
  <?php endif; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resetare Parolă - Mindful</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #444;
      --primary-hover: #333;
      --accent: #ffdd57;
      --bg: #ececec;
      --light-bg: #fff;
      --error: #e74c3c;
      --success: #27ae60;
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Roboto', sans-serif;
      background: var(--bg);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    header, footer {
      background: var(--light-bg);
      padding: 20px 0;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .header-container, .footer-container {
      width: 90%;
      max-width: 1200px;
      margin: auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .logo h1 {
      font-family: 'Montserrat', sans-serif;
      color: green;
    }
    .logo a {
      text-decoration: none;
    }
    .container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 80px 20px;
    }
    .box {
      background: var(--light-bg);
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 2px 20px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 500px;
      width: 100%;
    }
    .message {
      font-size: 1.1em;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .message.success {
      background-color: var(--success);
      color: #fff;
    }
    .message.error {
      background-color: var(--error);
      color: #fff;
    }
    .nav-btn {
      background: var(--primary);
      color: #fff;
      border: none;
      padding: 12px 25px;
      border-radius: 30px;
      cursor: pointer;
      transition: background 0.3s;
      font-family: 'Montserrat', sans-serif;
      font-size: 1em;
      text-decoration: none;
      display: inline-block;
      margin: 5px;
    }
    .nav-btn:hover {
      background: var(--primary-hover);
    }
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="logo">
        <h1><a href="index.html" style="color:green;">Mindful</a></h1>
      </div>
    </div>
  </header>
  <div class="container">
    <div class="box">
      <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
      <?php if ($messageType === "error" && $message === "Parolele nu se potrivesc."): ?>
        <a class="nav-btn" href="reset_form.php?token=<?php echo urlencode($tokenForLink); ?>">Înapoi</a>
      <?php endif; ?>
    </div>
  </div>
  <footer>
    <div class="footer-container" style="justify-content: center; text-align: center;">
      <p>© <?php echo date("Y"); ?> Mindful. Toate drepturile rezervate.</p>
    </div>
  </footer>
</body>
</html>
