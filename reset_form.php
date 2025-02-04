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

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT * FROM utilizatori WHERE reset_token = ?");
    $stmt->execute([$token]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($userData) {
        $currentDateTime = date("Y-m-d H:i:s");
        if ($currentDateTime > $userData['reset_token_expiration']) {
            echo "Link-ul de resetare a parolei a expirat.";
            exit();
        }
    } else {
        echo "Token invalid.";
        exit();
    }
} else {
    echo "Token lipsă.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resetare Parolă</title>
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
    .nav-right a {
      text-decoration: none;
      font-size: 1em;
      padding: 10px 20px;
      border-radius: 30px;
      background: var(--primary);
      color: #fff;
      transition: background 0.3s;
      margin-left: 10px;
    }
    .nav-right a:hover {
      background: var(--primary-hover);
    }
    .container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 120px 20px 80px;
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
    h2 {
      margin-bottom: 20px;
      font-family: 'Montserrat', sans-serif;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    label {
      font-weight: 600;
      font-family: 'Montserrat', sans-serif;
      margin-bottom: 8px;
      text-align: left;
      display: block;
    }
    .password-container {
      position: relative;
    }
    .password-container input {
      width: 100%;
      padding: 12px;
      padding-right: 40px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 1em;
    }
    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      user-select: none;
    }
    .strength-meter {
      height: 8px;
      width: 100%;
      background: #ccc;
      border-radius: 4px;
      margin-top: 5px;
    }
    .strength-meter-fill {
      height: 100%;
      width: 0%;
      background: red;
      border-radius: 4px;
      transition: width 0.3s;
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
    }
    .nav-btn:hover {
      background: var(--primary-hover);
    }
    footer {
      padding: 30px 0;
      text-align: center;
    }
    footer p {
      font-size: 0.9em;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-container">
      <div class="logo">
        <h1><a href="index.html" style="color:green;">Mindful</a></h1>
      </div>
      <nav class="nav-right">
        <a href="blog.html">Blog</a>
        <a href="signin.html">Intrare</a>
        <a href="signup.html">Înregistrare</a>
      </nav>
    </div>
  </header>
  <div class="container">
    <div class="box">
      <h2>Resetare Parolă</h2>
      <form action="update_password.php" method="POST">
        <!-- Token-ul este transmis ca field ascuns -->
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div>
          <label for="password">Parolă nouă:</label>
          <div class="password-container">
            <input type="password" name="password" id="password" required>
            <span class="toggle-password" onclick="togglePassword('password')">👁</span>
          </div>
          <div class="strength-meter" id="strength-meter">
            <div class="strength-meter-fill" id="strength-meter-fill"></div>
          </div>
        </div>
        <div>
          <label for="password_confirm">Confirmă parola:</label>
          <div class="password-container">
            <input type="password" name="password_confirm" id="password_confirm" required>
            <span class="toggle-password" onclick="togglePassword('password_confirm')">👁</span>
          </div>
        </div>
        <button type="submit" class="nav-btn">Resetează parola</button>
      </form>
    </div>
  </div>
  <footer>
    <div class="footer-container">
      <div class="contact-info">
        <h4 style="font-family:'Montserrat', sans-serif;">Contact</h4>
        <p>Email: mindfulcontact@gmail.com</p>
        <p>Telefon: 0748402864</p>
        <p>© 2024 Mindful</p>
      </div>
    </div>
  </footer>
  <script>
    function togglePassword(fieldId) {
      var field = document.getElementById(fieldId);
      field.type = field.type === "password" ? "text" : "password";
    }
    var passwordInput = document.getElementById('password');
    var strengthMeterFill = document.getElementById('strength-meter-fill');
    passwordInput.addEventListener('input', function() {
      var val = passwordInput.value, result = 0;
      if (val.length > 5) result++;
      if (val.match(/[a-z]/) && val.match(/[A-Z]/)) result++;
      if (val.match(/\d/)) result++;
      if (val.match(/[\W_]/)) result++;
      var width = (result / 4) * 100;
      strengthMeterFill.style.width = width + '%';
      if (result <= 1) {
        strengthMeterFill.style.background = 'red';
      } else if (result === 2) {
        strengthMeterFill.style.background = 'orange';
      } else if (result === 3) {
        strengthMeterFill.style.background = 'yellow';
      } else if (result === 4) {
        strengthMeterFill.style.background = 'green';
      }
    });
  </script>
</body>
</html>
