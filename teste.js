document.addEventListener("DOMContentLoaded", function () {
  var correctAnswers = {
    "0": "vibrația aerului",
    "1": "unitate de ritm din cuvânt",
    "2": "unitate de limbă",
    "3": "adevărat",
    "4": "una sau mai multe",
    "5": "ma – mă",
    "6": "a,b",
    "7": "silabe",
    "9": "adevărat",
    "10": "o",
    "11": "sibilant",
    "12": "adevărat",
    "13": "recunoașterea sunetelor, silabelor și cuvintelor",
    "14": "cuvintele,comunică,mesajul"
  };

  var quizFinished = localStorage.getItem("quizFinished") === "true";
  var storedTime = localStorage.getItem("timeRemaining");
  var totalTime = !quizFinished ? (storedTime !== null ? parseInt(storedTime, 10) : 300) : 0;
  var timerEl = document.getElementById("timer");
  var quizProgressBar = document.getElementById("quizProgressBar");
  var timerInterval;

  if (!quizFinished) {
    timerInterval = setInterval(function () {
      var minutes = String(Math.floor(totalTime / 60)).padStart(2, "0");
      var seconds = String(totalTime % 60).padStart(2, "0");
      timerEl.textContent = "Timp: " + minutes + ":" + seconds;
      localStorage.setItem("timeRemaining", totalTime);
      if (totalTime <= 0) {
        clearInterval(timerInterval);
        if (confirm("Timpul a expirat! Doriți să refaceți testul?")) {
          localStorage.removeItem("currentQuestion");
          localStorage.removeItem("timeRemaining");
          localStorage.removeItem("userAnswers");
          localStorage.removeItem("quizFinished");
          location.reload();
        } else {
          showSummary();
        }
      }
      totalTime--;
    }, 1000);
  } else {
    timerEl.textContent = "Timp: 00:00";
  }

  var quizQuestions = document.querySelectorAll(".quiz-question");
  var totalQuestions = quizQuestions.length;
  var currentQuestion = 0;
  var storedQuestion = localStorage.getItem("currentQuestion");
  if (storedQuestion !== null) currentQuestion = parseInt(storedQuestion, 10);
  var userAnswers = {};
  var storedUserAnswers = localStorage.getItem("userAnswers");
  if (storedUserAnswers !== null) userAnswers = JSON.parse(storedUserAnswers);

  populateAnswers();

  if (quizFinished) {
    showSummary();
  } else {
    hideAllQuestions();
    if (quizQuestions[currentQuestion]) {
      quizQuestions[currentQuestion].style.display = "block";
    }
    document.getElementById("prevBtn").disabled = (currentQuestion === 0);
    document.getElementById("nextBtn").textContent = (currentQuestion === totalQuestions - 1) ? "Submit" : "Următorul";
    updateProgress();
  }

  function hideAllQuestions() {
    quizQuestions.forEach(function (q) {
      q.style.display = "none";
    });
  }

  function updateProgress() {
    var percent = (currentQuestion / (totalQuestions - 1)) * 100;
    quizProgressBar.style.width = percent + "%";
  }

  function populateAnswers() {
    quizQuestions.forEach(function (q) {
      var qIndex = q.getAttribute("data-index");
      var type = q.getAttribute("data-type");
      var ans = userAnswers[qIndex];
      if (!ans) return;
      if (type === "mc" || type === "tf") {
        var options = q.querySelectorAll(".quiz-option");
        options.forEach(function (opt) {
          if (opt.textContent.trim().toLowerCase() === ans) {
            opt.classList.add("selected");
          }
        });
      } else if (type === "fib") {
        var input = q.querySelector(".fib-input");
        input.value = ans;
      } else if (type === "ms") {
        var checkboxes = q.querySelectorAll('input[type="checkbox"]');
        var arr = ans.split(",").map(function (v) { return v.trim(); });
        checkboxes.forEach(function (chk) {
          if (arr.indexOf(chk.value.trim().toLowerCase()) > -1) {
            chk.checked = true;
          }
        });
      } else if (type === "dd") {
        var dropzone = q.querySelector(".dropzone");
        var dragContainer = q.querySelector(".drag-container");
        var order = ans.split(",").map(function (v) { return v.trim(); });
        order.forEach(function (text) {
          var draggable;
          // Look for the matching element in the dropzone first
          draggable = dropzone.querySelector(".draggable");
          if (!draggable) {
            // If not there, look in the dragContainer
            var all = dragContainer.querySelectorAll(".draggable");
            all.forEach(function (el) {
              if (el.textContent.trim().toLowerCase() === text) {
                draggable = el;
              }
            });
          }
          if (draggable) {
            dropzone.appendChild(draggable);
          }
        });
      }
    });
  }

  var options = document.querySelectorAll(".quiz-option");
  options.forEach(function (option) {
    option.addEventListener("click", function () {
      var parentQ = option.closest(".quiz-question");
      var type = parentQ.getAttribute("data-type");
      if (type === "mc" || type === "tf") {
        var siblings = parentQ.querySelectorAll(".quiz-option");
        siblings.forEach(function (sib) {
          sib.classList.remove("selected");
        });
        option.classList.add("selected");
        var qIndex = parentQ.getAttribute("data-index");
        userAnswers[qIndex] = option.textContent.trim().toLowerCase();
        localStorage.setItem("userAnswers", JSON.stringify(userAnswers));
      }
    });
  });

  var fibInputs = document.querySelectorAll(".fib-input");
  fibInputs.forEach(function (input) {
    input.addEventListener("change", function () {
      var qIndex = input.closest(".quiz-question").getAttribute("data-index");
      userAnswers[qIndex] = input.value.trim().toLowerCase();
      localStorage.setItem("userAnswers", JSON.stringify(userAnswers));
    });
  });

  var msQuestions = document.querySelectorAll('[data-type="ms"]');
  msQuestions.forEach(function (msQ) {
    var qIndex = msQ.getAttribute("data-index");
    var checkboxes = msQ.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(function (chk) {
      chk.addEventListener("change", function () {
        var selected = [];
        checkboxes.forEach(function (box) {
          if (box.checked) selected.push(box.value);
        });
        userAnswers[qIndex] = selected.sort().join(",").toLowerCase();
        localStorage.setItem("userAnswers", JSON.stringify(userAnswers));
      });
    });
  });

  var ddQuestions = document.querySelectorAll('[data-type="dd"]');
  ddQuestions.forEach(function (q) {
    var dragContainer = q.querySelector('.drag-container');
    var dropzone = q.querySelector('.dropzone');
    var draggables = q.querySelectorAll('.draggable');
    draggables.forEach(function (draggable) {
      draggable.originalParent = dragContainer;
      draggable.addEventListener('mousedown', function (e) {
        e.preventDefault();
        var startX = e.clientX,
            startY = e.clientY;
        draggable.style.transition = '';
        function onMouseMove(ev) {
          var deltaX = ev.clientX - startX,
              deltaY = ev.clientY - startY;
          draggable.style.transform = "translate(" + deltaX + "px, " + deltaY + "px)";
        }
        document.addEventListener('mousemove', onMouseMove);
        function onMouseUp(ev) {
          document.removeEventListener('mousemove', onMouseMove);
          document.removeEventListener('mouseup', onMouseUp);
          var dzRect = dropzone.getBoundingClientRect();
          if (
            ev.clientX >= dzRect.left &&
            ev.clientX <= dzRect.right &&
            ev.clientY >= dzRect.top &&
            ev.clientY <= dzRect.bottom
          ) {
            dropzone.appendChild(draggable);
            draggable.style.transition = '';
            draggable.style.transform = '';
          } else {
            draggable.style.transition = 'transform 0.5s ease';
            draggable.style.transform = 'translate(0, 0)';
            draggable.addEventListener('transitionend', function te() {
              draggable.style.transition = '';
              draggable.style.transform = '';
              draggable.originalParent.appendChild(draggable);
              draggable.removeEventListener('transitionend', te);
            });
          }
          var qIndex = q.getAttribute("data-index");
          var children = dropzone.querySelectorAll('.draggable');
          var order = [];
          children.forEach(function (child) {
            order.push(child.textContent.trim().toLowerCase());
          });
          userAnswers[qIndex] = order.join(",");
          localStorage.setItem("userAnswers", JSON.stringify(userAnswers));
        }
        document.addEventListener('mouseup', onMouseUp);
      });
    });
  });

  var prevBtn = document.getElementById("prevBtn");
  var nextBtn = document.getElementById("nextBtn");

  function validateCurrent() {
    var cQ = quizQuestions[currentQuestion];
    var type = cQ.getAttribute("data-type");
    if (type === "mc" || type === "tf") {
      var sel = cQ.querySelector(".quiz-option.selected");
      if (!sel) {
        alert("Selectează un răspuns!");
        return false;
      }
    }
    if (type === "fib") {
      var inp = cQ.querySelector(".fib-input");
      if (inp.value.trim() === "") {
        alert("Completează răspunsul!");
        return false;
      }
    }
    if (type === "ms") {
      var c = cQ.querySelectorAll('input[type="checkbox"]:checked');
      if (c.length === 0) {
        alert("Selectează cel puțin o opțiune!");
        return false;
      }
    }
    if (type === "dd") {
      var dropzone = cQ.querySelector('.dropzone');
      var children = dropzone.querySelectorAll('.draggable');
      var expectedTotal = parseInt(dropzone.getAttribute("data-total"));
      if (children.length !== expectedTotal) {
        alert("Trage toate cuvintele în zona de plasare!");
        return false;
      }
      var order = [];
      children.forEach(function (child) {
        order.push(child.textContent.trim().toLowerCase());
      });
      var qIndex = cQ.getAttribute("data-index");
      userAnswers[qIndex] = order.join(",");
      localStorage.setItem("userAnswers", JSON.stringify(userAnswers));
    }
    return true;
  }

  nextBtn.addEventListener("click", function () {
    if (!validateCurrent()) return;
    if (currentQuestion === totalQuestions - 1) {
      showSummary();
    } else {
      quizQuestions[currentQuestion].style.display = "none";
      currentQuestion++;
      quizQuestions[currentQuestion].style.display = "block";
      prevBtn.disabled = (currentQuestion === 0);
      nextBtn.textContent = (currentQuestion === totalQuestions - 1) ? "Submit" : "Următorul";
      updateProgress();
      localStorage.setItem("currentQuestion", currentQuestion);
    }
  });

  prevBtn.addEventListener("click", function () {
    if (currentQuestion > 0) {
      quizQuestions[currentQuestion].style.display = "none";
      currentQuestion--;
      quizQuestions[currentQuestion].style.display = "block";
      nextBtn.textContent = "Următorul";
      prevBtn.disabled = (currentQuestion === 0);
      updateProgress();
      localStorage.setItem("currentQuestion", currentQuestion);
    }
  });

  function showSummary() {
    if (timerInterval) clearInterval(timerInterval);
    document.getElementById("quiz").style.display = "none";
    var summaryDiv = document.getElementById("summaryContainer");
    summaryDiv.innerHTML = "";
    var s = 0;
    quizQuestions.forEach(function (q) {
      var qIndex = q.getAttribute("data-index") || "";
      var qText = q.querySelector("h2").textContent;
      var type = q.getAttribute("data-type");
      var userAns = (userAnswers[qIndex] || "").trim();
      var rawCorr = correctAnswers.hasOwnProperty(qIndex) ? correctAnswers[qIndex] : "";
      var corrAns = rawCorr.toLowerCase().trim();
      if (userAns === corrAns) s++;
      var questionDiv = document.createElement("div");
      questionDiv.className = "summary-question";
      var qTitle = document.createElement("h2");
      qTitle.textContent = qText;
      questionDiv.appendChild(qTitle);
      if (type === "mc" || type === "tf") {
        var opts = q.querySelectorAll(".quiz-option");
        opts.forEach(function (opt) {
          var optText = opt.textContent.trim();
          var optDiv = document.createElement("div");
          optDiv.className = "summary-option";
          optDiv.textContent = optText;
          var isC = opt.getAttribute("data-correct") === "true";
          var sel = (userAns === optText.toLowerCase());
          if (sel && isC) {
            optDiv.classList.add("option-correct");
            var icon = document.createElement("span");
            icon.className = "icon";
            icon.textContent = "✔";
            optDiv.appendChild(icon);
          } else if (sel && !isC) {
            optDiv.classList.add("option-incorrect");
            var iconX = document.createElement("span");
            iconX.className = "icon";
            iconX.textContent = "✖";
            optDiv.appendChild(iconX);
            var e = document.createElement("div");
            e.className = "explanation";
            e.textContent = "Explicație: " + opt.getAttribute("data-explain");
            optDiv.appendChild(e);
          } else if (!sel && isC) {
            optDiv.classList.add("option-correct");
            var iC = document.createElement("span");
            iC.className = "icon";
            iC.textContent = "✔";
            optDiv.appendChild(iC);
          }
          questionDiv.appendChild(optDiv);
        });
      } else if (type === "fib") {
        var fibDiv = document.createElement("div");
        fibDiv.className = "summary-option";
        fibDiv.textContent = "Răspunsul tău: " + userAns + " | Răspuns corect: " + rawCorr;
        if (userAns === corrAns) fibDiv.classList.add("option-correct");
        else fibDiv.classList.add("option-incorrect");
        questionDiv.appendChild(fibDiv);
      } else if (type === "ms") {
        var msDiv = document.createElement("div");
        msDiv.className = "summary-option";
        msDiv.textContent = "Răspunsul tău: " + userAns + " | Răspuns corect: " + rawCorr;
        if (userAns === rawCorr.toLowerCase()) msDiv.classList.add("option-correct");
        else msDiv.classList.add("option-incorrect");
        questionDiv.appendChild(msDiv);
      } else if (type === "dd") {
        var ddDiv = document.createElement("div");
        ddDiv.className = "summary-option";
        ddDiv.textContent = "Răspunsul tău: " + userAns + " | Răspuns corect: " + rawCorr;
        if (userAns === corrAns) ddDiv.classList.add("option-correct");
        else ddDiv.classList.add("option-incorrect");
        questionDiv.appendChild(ddDiv);
      }
      summaryDiv.appendChild(questionDiv);
    });
    var grade = ((s / quizQuestions.length) * 10).toFixed(1);
    document.getElementById("resultText").textContent = "Punctajul tău: " + grade + " din 10";
    document.getElementById("summary").style.display = "block";
    var scoreBar = document.getElementById("scoreBar");
    var finalPercent = (grade / 10) * 100;
    scoreBar.style.width = finalPercent + "%";
    if (grade < 5) scoreBar.style.background = "#dc3545";
    else if (grade < 7) scoreBar.style.background = "#ffc107";
    else scoreBar.style.background = "#28a745";
    localStorage.setItem("quizFinished", "true");
  }

  var hintButtons = document.querySelectorAll(".hint-button");
  var hintModalBulb = document.getElementById("hintModalBulb");
  var hintModalEye = document.getElementById("hintModalEye");
  var hintModalDrag = document.getElementById("hintModalDrag");
  var closeModalBulb = document.getElementById("closeModalBulb");
  var closeModalEye = document.getElementById("closeModalEye");
  var closeModalDrag = document.getElementById("closeModalDrag");
  hintButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
      var parentQ = btn.closest(".quiz-question");
      var hintType = parentQ.getAttribute("data-hint");
      if (hintType === "bulb") hintModalBulb.style.display = "flex";
      else if (hintType === "eye") hintModalEye.style.display = "flex";
      else if (hintType === "drag") hintModalDrag.style.display = "flex";
    });
  });
  closeModalBulb.addEventListener("click", function () { hintModalBulb.style.display = "none"; });
  closeModalEye.addEventListener("click", function () { hintModalEye.style.display = "none"; });
  closeModalDrag.addEventListener("click", function () { hintModalDrag.style.display = "none"; });
  window.addEventListener("click", function (e) {
    if (e.target == hintModalBulb) hintModalBulb.style.display = "none";
    if (e.target == hintModalEye) hintModalEye.style.display = "none";
    if (e.target == hintModalDrag) hintModalDrag.style.display = "none";
  });

  function postComment(formId, commentsListId) {
    var form = document.getElementById(formId);
    form.addEventListener("submit", function (e) {
      e.preventDefault();
      var text = form.querySelector("textarea").value.trim();
      if (text === "") return;
      summaryCommentCounter++;
      var commentEl = document.createElement("div");
      commentEl.className = "comment";
      var img = document.createElement("img");
      img.src = "assets/pfp.jpg";
      var content = document.createElement("div");
      content.className = "comment-content";
      var header = document.createElement("div");
      header.className = "comment-header";
      var nameEl = document.createElement("span");
      nameEl.className = "name";
      nameEl.textContent = "Dan";
      var roleEl = document.createElement("span");
      roleEl.className = "role";
      roleEl.textContent = "administrator • profesor";
      var dateEl = document.createElement("span");
      dateEl.className = "date";
      var now = new Date();
      dateEl.textContent = now.toLocaleDateString() + " " + now.toLocaleTimeString();
      header.appendChild(nameEl);
      header.appendChild(roleEl);
      header.appendChild(dateEl);
      var commentTextEl = document.createElement("div");
      commentTextEl.className = "comment-text";
      commentTextEl.textContent = text;
      var actions = document.createElement("div");
      actions.className = "comment-actions";
      var likeBtn = document.createElement("button");
      likeBtn.innerHTML = '<i class="fas fa-thumbs-up"></i> 0';
      var dislikeBtn = document.createElement("button");
      dislikeBtn.innerHTML = '<i class="fas fa-thumbs-down"></i> 0';
      actions.appendChild(likeBtn);
      actions.appendChild(dislikeBtn);
      var replyToggle = document.createElement("button");
      replyToggle.className = "reply-toggle";
      replyToggle.textContent = "Reply";
      replyToggle.addEventListener("click", function () {
        var container = commentEl.querySelector(".reply-form-container");
        container.style.display = (container.style.display === "none" || container.style.display === "") ? "block" : "none";
      });
      var replyFormContainer = document.createElement("div");
      replyFormContainer.className = "reply-form-container";
      replyFormContainer.id = "reply-form-" + summaryCommentCounter;
      replyFormContainer.style.display = "none";
      var replyForm = document.createElement("form");
      replyForm.className = "reply-form";
      var replyTextarea = document.createElement("textarea");
      replyTextarea.placeholder = "Scrie un răspuns...";
      replyTextarea.required = true;
      var replyBtn = document.createElement("button");
      replyBtn.type = "submit";
      replyBtn.textContent = "Postează răspuns";
      replyForm.appendChild(replyTextarea);
      replyForm.appendChild(replyBtn);
      replyForm.addEventListener("submit", function (ev) {
        ev.preventDefault();
        var replyText = replyTextarea.value.trim();
        if (replyText === "") return;
        var replyEl = document.createElement("div");
        replyEl.className = "comment reply";
        var replyImg = document.createElement("img");
        replyImg.src = "assets/pfp.jpg";
        var replyContent = document.createElement("div");
        replyContent.className = "comment-content";
        var replyHeader = document.createElement("div");
        replyHeader.className = "comment-header";
        var replyName = document.createElement("span");
        replyName.className = "name";
        replyName.textContent = "Dan";
        var replyRole = document.createElement("span");
        replyRole.className = "role";
        replyRole.textContent = "administrator • profesor";
        var replyDate = document.createElement("span");
        replyDate.className = "date";
        var replyNow = new Date();
        replyDate.textContent = replyNow.toLocaleDateString() + " " + replyNow.toLocaleTimeString();
        replyHeader.appendChild(replyName);
        replyHeader.appendChild(replyRole);
        replyHeader.appendChild(replyDate);
        var replyCommentText = document.createElement("div");
        replyCommentText.className = "comment-text";
        replyCommentText.textContent = replyText;
        replyContent.appendChild(replyHeader);
        replyContent.appendChild(replyCommentText);
        replyEl.appendChild(replyImg);
        replyEl.appendChild(replyContent);
        var existingReplies = commentEl.querySelector(".comment-replies");
        if (!existingReplies) {
          existingReplies = document.createElement("div");
          existingReplies.className = "comment-replies";
          commentEl.appendChild(existingReplies);
        }
        existingReplies.appendChild(replyEl);
        replyForm.reset();
        replyFormContainer.style.display = "none";
      });
      replyFormContainer.appendChild(replyForm);
      content.appendChild(header);
      content.appendChild(commentTextEl);
      content.appendChild(actions);
      content.appendChild(replyToggle);
      content.appendChild(replyFormContainer);
      commentEl.appendChild(img);
      commentEl.appendChild(content);
      document.getElementById(commentsListId).appendChild(commentEl);
      form.reset();
    });
  }
  postComment("summaryCommentForm", "summaryCommentsList");
});

document.getElementById("resetQuiz").addEventListener("click", function(e) {
  e.preventDefault(); 
  localStorage.removeItem("currentQuestion");
  localStorage.removeItem("timeRemaining");
  localStorage.removeItem("userAnswers");
  localStorage.removeItem("quizFinished");
  location.reload(); 
});
