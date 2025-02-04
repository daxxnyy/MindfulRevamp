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
$section_id = 'lectie1';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lessonCommentText'])) {
  $comment = trim($_POST['lessonCommentText']);
  $parent_id = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') ? $_POST['parent_id'] : null;
  if ($comment !== '') {
    $stmt = $pdo->prepare("INSERT INTO comentarii (nume, comentariu, parent_id, section_id) VALUES (:nume, :comentariu, :parent_id, :section_id)");
    $stmt->execute([':nume' => 'Dan', ':comentariu' => $comment, ':parent_id' => $parent_id, ':section_id' => $section_id]);
  }
  header("Location: lectie1.php");
  exit();
}
$stmt = $pdo->prepare("SELECT * FROM comentarii WHERE section_id = :section_id ORDER BY created_at DESC");
$stmt->execute([':section_id' => $section_id]);
$commentsAll = $stmt->fetchAll(PDO::FETCH_ASSOC);
$comments = [];
foreach ($commentsAll as $c) {
  $parent = $c['parent_id'] ? $c['parent_id'] : 0;
  if (!isset($comments[$parent])) { $comments[$parent] = []; }
  $comments[$parent][] = $c;
}
function getRole($nume) {
  if ($nume === 'Dan') return 'administrator, profesor';
  return 'elev';
}
function renderComments($parent_id, $comments) {
  if (isset($comments[$parent_id])) {
    foreach ($comments[$parent_id] as $comm) {
      echo "<div class='comment' data-id='{$comm['id']}'>";
      echo "<img src='/MindfulSite/assets/pfp.jpg' alt='User'>";
      echo "<div class='comment-content'>";
      echo "<div class='comment-header'><span class='name'>" . htmlspecialchars($comm['nume']) . "</span><span class='role'> (" . getRole($comm['nume']) . ")</span><span class='date'>" . date("d-m-Y H:i", strtotime($comm['created_at'])) . "</span></div>";
      echo "<div class='comment-text'>" . nl2br(htmlspecialchars($comm['comentariu'])) . "</div>";
      echo "<div class='comment-actions'>";
      echo "<button class='vote-button' data-id='{$comm['id']}' data-vote='like'><span class='like-icon'>&#x1F44D;</span> (<span class='like-count'>{$comm['likes']}</span>)</button>";
      echo "<button class='vote-button' data-id='{$comm['id']}' data-vote='dislike'><span class='dislike-icon'>&#x1F44E;</span> (<span class='dislike-count'>{$comm['dislikes']}</span>)</button>";
      if (empty($comm['parent_id'])) {
        echo "<button class='reply-toggle' data-id='{$comm['id']}'>Reply</button>";
      }
      echo "</div>";
      if (empty($comm['parent_id'])) {
        echo "<div class='reply-form-container' id='reply-form-{$comm['id']}' style='display:none;'>";
        echo "<form method='POST' action='lectie1.php' class='reply-form'>";
        echo "<textarea name='lessonCommentText' placeholder='Scrie un răspuns...' required></textarea>";
        echo "<input type='hidden' name='parent_id' value='{$comm['id']}'>";
        echo "<button type='submit'>Postează răspuns</button>";
        echo "</form>";
        echo "</div>";
      }
      if (isset($comments[$comm['id']])) {
        echo "<div class='comment-replies'>";
        renderComments($comm['id'], $comments);
        echo "</div>";
      }
      echo "</div>";
      echo "</div>";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lectia 1 - Sunetul. Silaba. Cuvântul. - Mindful</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    :root { --bg: #f7f9fc; --text: #333; --primary: #0077cc; --primary-hover: #005fa3; --accent: #ffdd57; --header-bg: #fff; }
    html { scroll-behavior: smooth; }
    body { font-family: 'Poppins', sans-serif; background: var(--bg); color: var(--text); }
    .container { width: 90%; max-width: 1200px; margin: 0 auto; }
    header { background: var(--header-bg); padding: 20px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); position: fixed; top: 0; width: 100%; z-index: 1000; }
    .header-container { display: flex; justify-content: space-between; align-items: center; }
    .logo h1 { font-family: 'Roboto', sans-serif; font-size: 2em; }
    .logo-link { text-decoration: none; color: var(--primary); }
    nav { display: flex; gap: 20px; }
    nav a { text-decoration: none; color: var(--primary); font-weight: 500; padding: 8px 16px; border-radius: 20px; transition: background 0.3s, transform 0.3s; }
    nav a:hover { background: var(--primary); color: #fff; transform: translateY(-2px); }
    main { padding: 120px 20px 60px; }
    footer { background: #333; color: #fff; padding: 40px 20px; text-align: center; }
    footer h4 { font-family: 'Roboto', sans-serif; margin-bottom: 10px; }
    .lesson-content { animation: fadeInUp 1s ease; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
    .lesson-section { margin-bottom: 40px; animation: slideIn 0.8s ease forwards; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
    .lesson-section h2 { font-family: 'Roboto', sans-serif; margin-bottom: 20px; font-size: 2em; text-align: center; }
    .lesson-section p { font-size: 1.1em; line-height: 1.8; margin-bottom: 20px; }
    .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: transform 0.5s ease; }
    .video-container:hover { transform: scale(1.03); }
    .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
    .quiz-button { display: inline-block; margin-top: 40px; padding: 15px 30px; background: var(--primary); color: #fff; border: none; border-radius: 50px; font-size: 1.2em; text-decoration: none; text-align: center; transition: background 0.3s, transform 0.3s; animation: pulse 2s infinite; }
    .quiz-button:hover { background: var(--primary-hover); transform: translateY(-3px); }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
    .comments-section { margin-top: 40px; }
    .comments-section h3 { font-family: 'Roboto', sans-serif; margin-bottom: 20px; font-size: 1.5em; text-align: center; }
    .comment { border-bottom: 1px solid #ddd; padding: 15px 0; display: flex; gap: 15px; animation: fadeIn 0.5s ease; margin-bottom: 10px; }
    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    .comment img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .comment-content { flex: 1; position: relative; }
    .comment-header { display: flex; align-items: center; margin-bottom: 5px; }
    .comment-header .name { font-weight: 700; }
    .comment-header .role { margin-left: 8px; font-size: 0.9em; color: #555; }
    .comment-header .date { margin-left: auto; font-size: 0.8em; color: #aaa; }
    .comment-text { margin-bottom: 10px; line-height: 1.6; }
    .comment-actions button { background: var(--primary); color: #fff; border: none; padding: 5px 10px; margin-right: 5px; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
    .comment-actions button:hover { background: var(--primary-hover); }
    .like-icon { color: green; }
    .dislike-icon { color: red; }
    .reply-toggle { background: transparent; color: var(--primary); border: 1px solid var(--primary); padding: 4px 8px; border-radius: 4px; }
    .reply-form-container { margin-top: 10px; animation: fadeIn 0.3s ease; }
    .reply-form-container textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; margin-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 1em; }
    .reply-form-container button { padding: 6px 12px; background: var(--accent); border: none; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
    .reply-form-container button:hover { background: darkorange; }
    #lessonCommentForm textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; resize: vertical; margin-bottom: 5px; font-family: 'Poppins', sans-serif; font-size: 1.1em; }
    #lessonCommentForm button { padding: 10px 20px; background: var(--primary); color: #fff; border: none; border-radius: 4px; cursor: pointer; transition: background 0.3s; }
    #lessonCommentForm button:hover { background: var(--primary-hover); }
    .comment-replies { margin-left: 50px; padding-left: 15px; border-left: 2px solid #eee; margin-top: 10px; }
  </style>
</head>
<body>
  <header>
    <div class="container header-container">
      <div class="logo">
        <h1><a href="index.html" class="logo-link">Mindful</a></h1>
      </div>
      <nav>
        <a href="blog.html">Blog</a>
        <a href="lectie1.php">Lectii</a>
      </nav>
    </div>
  </header>
  <main>
    <div class="container lesson-content">
      <div class="lesson-section">
        <h2>Lectia 1: Sunetul. Silaba. Cuvântul.</h2>
        <p>
          În această lecție de comunicare pentru clasa I vei învăța conceptele fundamentale:<br><br>
          <strong>Sunetul</strong> reprezintă vibrațiile aerului, care ne permit să auzim. Fiecare sunet are o frecvență specifică și se percepe prin urechi.<br><br>
          <strong>Silaba</strong> este unitatea de ritm din cuvânt. Un cuvânt poate fi format din una sau mai multe silabe. De exemplu, cuvântul "copil" se împarte în "co-pil".<br><br>
          <strong>Cuvântul</strong> este o unitate de limbă alcătuită din una sau mai multe silabe. Cuvintele stau la baza comunicării și ne ajută să exprimăm idei.
        </p>
      </div>
      <div class="lesson-section">
        <div class="video-container">
          <iframe src="https://www.youtube.com/embed/3UIV0WoQvD8" frameborder="0" allowfullscreen></iframe>
        </div>
      </div>
      <div class="lesson-section">
        <h2>Exerciții și Mini-jocuri</h2>
        <p>
          <strong>Exercițiul 1:</strong> Citește propoziția: "Mama mea are un măr roșu". Împarte cuvântul "măr" în silabe. Răspunsul corect este "măr" (cuvânt monosilabic).<br><br>
          <strong>Exercițiul 2:</strong> Alege un cuvânt simplu și împarte-l în silabe. De exemplu, cuvântul "copil" se împarte în "co-pil".<br><br>
          <strong>Exercițiul 3:</strong> Gândește-te la importanța înțelegerii modului în care se formează cuvintele pentru a comunica clar și eficient. Notează-ți ideile!
        </p>
      </div>
      <div class="lesson-section" style="text-align:center;">
        <a class="quiz-button" href="test1.php">Începe Testul</a>
      </div>
      <div class="comments-section">
        <h3>Comentarii</h3>
        <div id="commentsContainer">
          <?php renderComments(0, $comments); ?>
        </div>
        <form id="lessonCommentForm" method="POST" action="lectie1.php">
          <textarea id="lessonCommentText" name="lessonCommentText" placeholder="Scrie un comentariu..." required></textarea>
          <input type="hidden" name="parent_id" value="">
          <button type="submit">Postează</button>
        </form>
      </div>
    </div>
  </main>
  <footer>
    <div class="container">
      <div class="contact-info">
        <h4>Contact</h4>
        <p>Email: mindfulcontact@gmail.com</p>
        <p>Telefon: 0748402864</p>
        <p>© 2025 Mindful</p>
      </div>
    </div>
  </footer>
  <script>
    document.querySelectorAll('.vote-button').forEach(function(button){
      button.addEventListener('click', function(){
        let commentId = this.getAttribute('data-id');
        let vote = this.getAttribute('data-vote');
        fetch('/MindfulSite/vote.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'comment_id=' + commentId + '&vote=' + vote })
        .then(response => response.json())
        .then(data => {
          if(data.success){
            let parent = this.parentElement;
            parent.querySelector("button[data-vote='like'] .like-count").innerText = data.likes;
            parent.querySelector("button[data-vote='dislike'] .dislike-count").innerText = data.dislikes;
          }
        });
      });
    });
    document.querySelectorAll('.reply-toggle').forEach(function(button){
      button.addEventListener('click', function(){
        let id = this.getAttribute('data-id');
        let container = document.getElementById('reply-form-' + id);
        container.style.display = container.style.display === 'none' ? 'block' : 'none';
      });
    });
  </script>
</body>
</html>
