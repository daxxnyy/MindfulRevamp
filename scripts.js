document.addEventListener('DOMContentLoaded', function(){
  const introVideo = document.getElementById('intro-video');
  const videos = ['assets/mathvideo.mp4', 'assets/chemistryvideo.mp4', 'assets/historyvideo.mp4'];
  let videoIndex = 0;
  introVideo.addEventListener('ended', function(){
    videoIndex = (videoIndex + 1) % videos.length;
    introVideo.src = videos[videoIndex];
    introVideo.load();
    introVideo.play();
  });
  const feedbackForm = document.getElementById('feedback-form');
  if(feedbackForm){
    feedbackForm.addEventListener('submit', handleFormSubmit);
    function handleFormSubmit(event){
      event.preventDefault();
      const formData = new FormData(event.target);
      if(!formData.get('rating')){
        showErrorMessage("Vă rugăm să selectați cel puțin o stea înainte de a trimite feedback-ul.");
        return;
      }
      fetch(event.target.action, {
        method: event.target.method,
        body: formData,
        headers: {'Accept': 'application/json'}
      })
      .then(response => {
        if(response.ok) {
          showSuccessMessage();
        } else {
          response.json().then(data => {
            if(data.errors) alert(data.errors.map(err => err.message).join(", "));
            else alert("Oops! A apărut o eroare la trimiterea formularului");
          });
        }
      })
      .catch(() => alert("Oops! A apărut o eroare la trimiterea formularului"));
    }
    function showSuccessMessage(){
      const successMessage = document.getElementById('success-message');
      successMessage.classList.remove('hidden');
      setTimeout(() => {
        successMessage.classList.add('hidden');
        feedbackForm.reset();
      }, 3000);
    }
    function showErrorMessage(message){
      const errorMessage = document.getElementById('error-message');
      errorMessage.textContent = message;
      errorMessage.classList.remove('hidden');
      setTimeout(() => {
        errorMessage.classList.add('hidden');
      }, 3000);
    }
  }
  const faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(item => {
    item.querySelector('.faq-question').addEventListener('click', () => {
      const isActive = item.classList.contains('active');
      faqItems.forEach(other => {
        other.classList.remove('active');
        other.setAttribute('aria-expanded', 'false');
      });
      if(!isActive){
        item.classList.add('active');
        item.setAttribute('aria-expanded', 'true');
      }
    });
  });
});
window.addEventListener('scroll', function(){
  const header = document.getElementById('header');
  if(window.scrollY > 50){
    header.classList.add('scrolled');
  } else {
    header.classList.remove('scrolled');
  }
});
