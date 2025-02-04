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