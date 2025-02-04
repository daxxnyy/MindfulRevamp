document.addEventListener("DOMContentLoaded", function(){
    const posts = document.querySelectorAll(".post-detail");
    posts.forEach(post => {
      post.style.opacity = 1;
      post.style.transform = "translateY(0)";
    });
    const header = document.getElementById("header");
    window.addEventListener("scroll", function(){
      if(window.scrollY > 50) { header.classList.add("scrolled"); } else { header.classList.remove("scrolled"); }
    });
  });
  