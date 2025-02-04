<?php
session_start();
$host = 'localhost';
$dbname = 'inregistrare_moderna';
$user = 'root';
$pass = '';

  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$section_id = 'test1';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lessonCommentText'])) {
  $comment = trim($_POST['lessonCommentText']);
  $parent_id = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '') ? $_POST['parent_id'] : null;
  if ($comment !== '') {
    $stmt = $pdo->prepare("INSERT INTO comentarii (nume, comentariu, parent_id, section_id) VALUES (:nume, :comentariu, :parent_id, :section_id)");
    $stmt->execute([':nume' => 'Dan', ':comentariu' => $comment, ':parent_id' => $parent_id, ':section_id' => $section_id]);
  }
  header("Location: test1.php");
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
  if ($nume === 'Dan') return 'administrator • profesor';
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
        echo "<form method='POST' action='test1.php' class='reply-form'>";
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
  <title>Test 1 - Sunetul. Silaba. Cuvântul. - Mindful</title>
  <link rel="stylesheet" href="/MindfulSite/teste.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
  <header>
    <div class="container header-container">
      <div class="logo">
        <h1><a href="index.html" class="logo-link">Mindful</a></h1>
      </div>
      <nav>
  <a href="lectie1.php">Iesire</a>
  <a href="#" id="resetQuiz">Reia</a>
</nav>
    </div>
  </header>
  <main>
    <div class="container">
      <div class="timer" id="timer">Timp: 05:00</div>
      <div class="quiz-progress">
        <div class="progress-bar-container">
          <div class="progress-bar" id="quizProgressBar"></div>
        </div>
      </div>
      <div class="quiz-container" id="quiz">
        <div class="quiz-question" data-index="0" data-type="mc" data-hint="bulb">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 1: Ce este sunetul?</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Sunetul este rezultatul vibrațiilor aerului.">Vibrația aerului</div>
            <div class="quiz-option" data-correct="false" data-explain="Culoarea nu poate fi auzită.">O culoare</div>
            <div class="quiz-option" data-correct="false" data-explain="Gustul nu se percepe prin auz.">Un gust</div>
            <div class="quiz-option" data-correct="false" data-explain="Mirosul nu este un sunet.">Un miros</div>
          </div>
        </div>
        <div class="quiz-question" data-index="1" data-type="mc" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 2: Ce este o silabă?</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Silaba este unitatea de ritm și sunet din cuvânt.">Unitate de ritm din cuvânt</div>
            <div class="quiz-option" data-correct="false" data-explain="O literă este mult mai mică decât o silabă.">O literă</div>
            <div class="quiz-option" data-correct="false" data-explain="Un cuvânt este format din silabe, nu este sinonim cu silaba.">Un cuvânt</div>
            <div class="quiz-option" data-correct="false" data-explain="Punctuația nu reprezintă silabe.">Un semn de punctuație</div>
          </div>
        </div>
        <div class="quiz-question" data-index="2" data-type="mc" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 3: Ce este un cuvânt?</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Un cuvânt este o unitate de limbă alcătuită din una sau mai multe silabe.">Unitate de limbă</div>
            <div class="quiz-option" data-correct="false" data-explain="O silabă este o parte a cuvântului, nu întregul.">O silabă</div>
            <div class="quiz-option" data-correct="false" data-explain="O propoziție se compune din mai multe cuvinte.">O propoziție</div>
            <div class="quiz-option" data-correct="false" data-explain="O frază este alcătuită din propoziții.">O frază</div>
          </div>
        </div>
        <div class="quiz-question" data-index="3" data-type="tf" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 4: Vibrația aerului produce sunet. (Adevărat/Fals)</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect, vibrația aerului este esențială pentru producerea sunetului.">Adevărat</div>
            <div class="quiz-option" data-correct="false" data-explain="Fals: vibrațiile aerului sunt necesare pentru sunet.">Fals</div>
          </div>
        </div>
        <div class="quiz-question" data-index="4" data-type="fib" data-hint="eye" style="display:none;">
          <button class="hint-button"><i class="fas fa-eye"></i></button>
          <h2>Întrebarea 5: Un cuvânt este alcătuit din ______ silabe.</h2>
          <div class="note">Introduceți un răspuns textual.</div>
          <input type="text" class="fib-input" placeholder="Scrie răspunsul aici">
        </div>
        <div class="quiz-question" data-index="5" data-type="mc" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 6: Cum se împarte cuvântul "mamă" în silabe?</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect: 'ma' și 'mă'.">Ma – Mă</div>
            <div class="quiz-option" data-correct="false" data-explain="Nu: 'mam' și 'ă' nu este corect.">Mam – Ă</div>
            <div class="quiz-option" data-correct="false" data-explain="Nu: 'm' și 'amă' nu este corect.">M – Amă</div>
            <div class="quiz-option" data-correct="false" data-explain="Nu: 'ma' și 'ma' nu este corect.">Ma – Ma</div>
          </div>
        </div>
        <div class="quiz-question" data-index="6" data-type="ms" data-hint="eye" style="display:none;">
          <button class="hint-button"><i class="fas fa-eye"></i></button>
          <h2>Întrebarea 7: Selectează afirmațiile adevărate despre un cuvânt.</h2>
          <div class="note">Puteți selecta mai multe opțiuni.</div>
          <div class="quiz-options">
            <div class="ms-option">
              <input type="checkbox" value="A" data-correct="true" data-explain="Un cuvânt este format din silabe.">
              <span>Este format din silabe</span>
            </div>
            <div class="ms-option">
              <input type="checkbox" value="B" data-correct="true" data-explain="Cuvintele pot fi monosilabice.">
              <span>Poate fi monosilabic</span>
            </div>
            <div class="ms-option">
              <input type="checkbox" value="C" data-correct="false" data-explain="Nu toate cuvintele sunt formate din cel puțin două silabe.">
              <span>Este întotdeauna format din cel puțin două silabe</span>
            </div>
            <div class="ms-option">
              <input type="checkbox" value="D" data-correct="false" data-explain="Cuvintele sunt formate din litere, deci afirmația este falsă.">
              <span>Nu este alcătuit din litere</span>
            </div>
          </div>
        </div>
        <div class="quiz-question" data-index="7" data-type="mc" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 8: Pentru a rosti un cuvânt, trebuie să combinăm...</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect: cuvântul se formează prin combinarea silabelor.">Silabe</div>
            <div class="quiz-option" data-correct="false" data-explain="Literele sunt elemente de bază, dar nu unități de rostire.">Litere</div>
            <div class="quiz-option" data-correct="false" data-explain="Numerele nu au legătură cu formarea cuvintelor.">Numere</div>
            <div class="quiz-option" data-correct="false" data-explain="Semnele de punctuație nu formează cuvinte.">Semne de punctuație</div>
          </div>
        </div>
        <div class="quiz-question" data-index="9" data-type="tf" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 9: O propoziție este formată din cuvinte. (Adevărat/Fals)</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect, propoziția este alcătuită din cuvinte.">Adevărat</div>
            <div class="quiz-option" data-correct="false" data-explain="Fals: propoziția trebuie să aibă cuvinte.">Fals</div>
          </div>
        </div>
        <div class="quiz-question" data-index="10" data-type="fib" data-hint="eye" style="display:none;">
          <button class="hint-button"><i class="fas fa-eye"></i></button>
          <h2>Întrebarea 10: Completează: Fiecare cuvânt are cel puțin _____ silabă.</h2>
          <div class="note">Introduceți un răspuns textual.</div>
          <input type="text" class="fib-input" placeholder="Scrie răspunsul aici">
        </div>
        <div class="quiz-question" data-index="11" data-type="mc" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 11: Sunetul "s" din cuvântul "soare" este...</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect: este un sunet sibilant.">Sibilant</div>
            <div class="quiz-option" data-correct="false" data-explain="Nu este vocalic.">Vocalic</div>
            <div class="quiz-option" data-correct="false" data-explain="Nu este aspru.">Aspru</div>
            <div class="quiz-option" data-correct="false" data-explain="Nu este nazal.">Nazal</div>
          </div>
        </div>
        <div class="quiz-question" data-index="12" data-type="tf" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 12: Cuvântul "mamă" se împarte corect în silabe "ma-mă". (Adevărat/Fals)</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect: 'ma-mă' este forma corectă.">Adevărat</div>
            <div class="quiz-option" data-correct="false" data-explain="Fals: altă împărțire nu este corectă.">Fals</div>
          </div>
        </div>
        <div class="quiz-question" data-index="13" data-type="mc" data-hint="bulb" style="display:none;">
          <button class="hint-button"><i class="fas fa-lightbulb"></i></button>
          <h2>Întrebarea 13: Comunicarea începe cu...</h2>
          <div class="quiz-options">
            <div class="quiz-option" data-correct="true" data-explain="Corect: recunoașterea sunetelor, silabelor și cuvintelor este baza comunicării.">Recunoașterea sunetelor, silabelor și cuvintelor</div>
            <div class="quiz-option" data-correct="false" data-explain="Culoarea cuvintelor nu contează.">Culoarea cuvintelor</div>
            <div class="quiz-option" data-correct="false" data-explain="Forma literelor nu este esențială.">Forma literelor</div>
            <div class="quiz-option" data-correct="false" data-explain="Propozițiile sunt mai complexe decât elementul de bază.">Propoziții</div>
          </div>
        </div>
        <div class="quiz-question" data-index="14" data-type="dd" data-hint="drag" style="display:none;">
          <button class="hint-button"><i class="fas fa-arrows-alt"></i></button>
          <h2>Întrebarea 14: Aranjează cuvintele pentru a forma propoziția corectă: "Cuvintele comunică mesajul"</h2>
          <div class="note">Trage și aranjează cuvintele în ordinea corectă.</div>
          <div class="drag-container">
            <div class="draggable" data-order="2">comunică</div>
            <div class="draggable" data-order="3">mesajul</div>
            <div class="draggable" data-order="1">Cuvintele</div>
          </div>
          <div class="dropzone" data-total="3">
            <p>Trage cuvintele aici în ordine</p>
          </div>
        </div>
        <div class="nav-buttons">
          <button id="prevBtn" class="nav-btn" disabled>Înapoi</button>
          <button id="nextBtn" class="nav-btn">Următorul</button>
        </div>
      </div>
      <div id="summary">
        <div class="result-text" id="resultText"></div>
        <div class="score-bar-container" id="scoreBarContainer">
          <div class="score-bar" id="scoreBar"></div>
        </div>
        <div class="summary-container" id="summaryContainer"></div>
        <div class="comments-section">
          <h3>Comentarii</h3>
          <div id="summaryCommentsList">
            <?php renderComments(0, $comments); ?>
          </div>
          <form id="lessonCommentForm" method="POST" action="test1.php">
            <textarea id="lessonCommentText" name="lessonCommentText" placeholder="Scrie un comentariu..." required></textarea>
            <input type="hidden" name="parent_id" value="">
            <button type="submit">Postează</button>
          </form>
        </div>
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
  <div class="modal" id="hintModalBulb">
    <div class="modal-content">
      <button class="close-modal" id="closeModalBulb">&times;</button>
      <div class="modal-icon" style="color:#0077cc;"><i class="fas fa-lightbulb"></i></div>
      <div class="modal-title">Capacitatea Înțelegere - Răspuns</div>
      <div class="modal-text">
        Această întrebare antrenează capacitatea de a înțelege informații. 
        Prin înțelegere, ne referim la abilitatea ta de a reține și interpreta corect 
        ceea ce citești sau auzi. 
        <br><br>
        Ex: Dacă înveți despre un eveniment istoric, poți identifica nu doar data, ci și 
        cauzele și consecințele acestui eveniment, integrându-le în răspunsul tău.
      </div>
    </div>
  </div>
  <div class="modal" id="hintModalEye">
    <div class="modal-content">
      <button class="close-modal" id="closeModalEye">&times;</button>
      <div class="modal-icon" style="color:#0077cc;"><i class="fas fa-eye"></i></div>
      <div class="modal-title">Capacitatea Observare - Răspuns</div>
      <div class="modal-text">
        Această întrebare antrenează capacitatea de a observa și de a deduce informații 
        chiar și din date parțiale sau incomplete, punând accent pe atenția la detalii. 
        <br><br>
        Ex: Dacă observi un fragment de text sau un set de date, răspunsul corect 
        implică să evidențiezi elementele cheie, să le pui în context și să tragi 
        concluziile corecte pe baza observațiilor tale.
      </div>
    </div>
  </div>
  <div class="modal" id="hintModalDrag">
    <div class="modal-content">
      <button class="close-modal" id="closeModalDrag">&times;</button>
      <div class="modal-icon" style="color:#0077cc;"><i class="fas fa-arrows-alt"></i></div>
      <div class="modal-title">Capacitatea Organizare - Răspuns</div>
      <div class="modal-text">
        Această întrebare antrenează capacitatea de a organiza elementele pentru a forma o propoziție coerentă. 
        Trage cuvintele în ordinea corectă pentru a construi propoziția: "Cuvintele comunică mesajul". 
        Reflectează asupra logicii și structurii limbajului atunci când alegi poziția fiecărui cuvânt.
      </div>
    </div>
  </div>
  <script src="/MindfulSite/teste.js"></script>
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
</html