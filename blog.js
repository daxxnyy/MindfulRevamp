document.addEventListener("DOMContentLoaded", function(){
    const posts = document.querySelectorAll(".post");
    posts.forEach(post => {
      const dateSpan = document.createElement("span");
      dateSpan.classList.add("post-date");
      dateSpan.textContent = new Date().toLocaleDateString("ro-RO", { weekday: "long", year:"numeric", month:"long", day:"numeric" });
      post.querySelector(".post-content").appendChild(dateSpan);
    });
    const observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if(entry.isIntersecting){
          entry.target.classList.add("visible");
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    posts.forEach(post => {
      observer.observe(post);
    });
  });
  