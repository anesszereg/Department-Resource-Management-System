const sign_in_btn = document.querySelector("#sign-in-btn");
const sign_up_btn = document.querySelector("#sign-up-btn");
const container = document.querySelector(".container");

sign_up_btn.addEventListener("click", () => {
  container.classList.add("sign-up-mode");
});
sign_in_btn.addEventListener("click", () => {
  container.classList.remove("sign-up-mode");
});

// Wait for the window to load before starting the animation
window.addEventListener('load', function() {
  const title = document.querySelector('.tittle');
  
  // Start the animation after a small delay to make it smooth
  setTimeout(function() {
    // Fade in and move the title up
    title.style.opacity = 1;
    title.style.transform = 'translateY(0)';
    
    // Add a bounce effect using a setTimeout to delay the bounce
    setTimeout(function() {
      title.style.transition = 'transform 0.2s ease-out';
      title.style.transform = 'translateY(-10px)'; // Bounce up slightly
      
      // After bounce, return to the original position
      setTimeout(function() {
        title.style.transition = 'transform 0.3s ease-in';
        title.style.transform = 'translateY(0)';
      }, 200); // Timing for bounce down
    }, 1000); // Timing for the initial animation to finish before bouncing
  }, 200); // Initial delay before starting the animation
});






































