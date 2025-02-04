<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'C:/Users/BOGDAN/OneDrive/Desktop/PHPMailer-master/src/Exception.php';
require 'C:/Users/BOGDAN/OneDrive/Desktop/PHPMailer-master/src/PHPMailer.php';
require 'C:/Users/BOGDAN/OneDrive/Desktop/PHPMailer-master/src/SMTP.php';

$host = 'localhost';
$dbname = 'inregistrare_moderna';
$user = 'root';
$pass = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Conexiunea la baza de date a eșuat: " . $e->getMessage());
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $message = "Email este necesar!";
        $messageType = "error";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE email = ?");
        $stmt->execute([$email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userData) {
            $token = bin2hex(random_bytes(16));
            $expiration = date("Y-m-d H:i:s", strtotime('+1 hour'));
            $stmt = $pdo->prepare("UPDATE utilizatori SET reset_token = ?, reset_token_expiration = ? WHERE email = ?");
            $stmt->execute([$token, $expiration, $email]);
            $resetLink = "http://localhost/MindfulSite/reset_form.php?token=" . $token;
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'iuoanbogdancioroiu@gmail.com';
                $mail->Password = 'gfzg nqyr qzcy hwhm';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;
                $mail->setFrom('your-email@gmail.com', 'Mindful');
                $mail->addAddress($email, $userData['nume']);
                $mail->isHTML(true);
                $mail->Subject = 'Resetare Parolă - Mindful';
                $mail->Body = "<h2>Resetare Parolă</h2><p>Click <a href='$resetLink'>aici</a> pentru a reseta parola. Link-ul este valabil timp de 1 oră.</p>";
                $mail->send();
                $message = "Email trimis cu succes. Verifică-ți inboxul.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Emailul nu a putut fi trimis. Mailer Error: " . $mail->ErrorInfo;
                $messageType = "error";
            }
        } else {
            $message = "Nu există cont asociat acestui email.";
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
      <?php if(!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
      <?php endif; ?>
      <a class="nav-btn" href="index.html">Înapoi la pagina principală</a>
    </div>
  </div>
  <footer>
    <div class="footer-container" style="justify-content: center; text-align: center;">
      <p>© <?php echo date("Y"); ?> Mindful. Toate drepturile rezervate.</p>
    </div>
  </footer>
</body>
</html>
