<?php
session_start();
$host='localhost';
$dbname='inregistrare_moderna';
$user='root';
$pass='';
try {
  $pdo=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$user,$pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){ die("Connection failed: ".$e->getMessage()); }
if($_SERVER['REQUEST_METHOD']==='POST'){
  $userType=isset($_POST['userType'])?$_POST['userType']:'';
  $metoda_signup=isset($_POST['metoda_signup'])?$_POST['metoda_signup']:'';
  $nume=isset($_POST['nume'])?trim($_POST['nume']):'';
  $telefon=isset($_POST['telefon'])?trim($_POST['telefon']):'';
  $email=isset($_POST['email'])?trim($_POST['email']):'';
  $parola=isset($_POST['parola'])?$_POST['parola']:'';
  $parola_repetata=isset($_POST['parola_repetata'])?$_POST['parola_repetata']:'';
  if($metoda_signup!=='email' && $metoda_signup!=='simplu'){
    die("Metoda de înregistrare invalidă. <a href='signup.html'>Înapoi</a>");
  }
  if($metoda_signup==='email' && empty($email)){
    die("Email-ul este necesar pentru metoda Email. <a href='javascript:history.back()'>Înapoi</a>");
  }
  if($metoda_signup==='simplu'){
    $email=null;
  }
  if($parola!==$parola_repetata){ die("Parolele nu corespund. <a href='javascript:history.back()'>Înapoi</a>"); }
  if(empty($userType)||empty($metoda_signup)||empty($nume)||empty($telefon)||empty($parola)){
    die("Toate câmpurile sunt necesare. <a href='javascript:history.back()'>Înapoi</a>");
  }
  if(!is_null($email)){
    $stmt=$pdo->prepare("SELECT id FROM utilizatori WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()){
      die("Email-ul este deja înregistrat. <a href='signin.html'>Autentificare</a>");
    }
  }
  $parola_hash=password_hash($parola, PASSWORD_DEFAULT);
  $sql="INSERT INTO utilizatori (userType, metoda_signup, nume, telefon, email, parola) VALUES (:userType, :metoda_signup, :nume, :telefon, :email, :parola)";
  $stmt=$pdo->prepare($sql);
  $params=[
    ':userType'=>$userType,
    ':metoda_signup'=>$metoda_signup,
    ':nume'=>$nume,
    ':telefon'=>$telefon,
    ':email'=>$email,
    ':parola'=>$parola_hash
  ];
  try{
    $stmt->execute($params);
    header("Location: principala.html");
    exit();
  } catch(PDOException $e){ die("Eroare la înregistrare: ".$e->getMessage()); }
} else { header("Location: signup.html"); exit(); }
?>
