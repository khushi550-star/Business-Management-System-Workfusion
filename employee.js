// Select all circle icons
const circles = document.querySelectorAll('.icon-circle');

circles.forEach(circle => {
  // Add glowing effect on hover
  circle.addEventListener('mouseenter', () => {
    circle.classList.add('glow');
  });

  circle.addEventListener('mouseleave', () => {
    circle.classList.remove('glow');
  });

  // Add bounce effect on click
  circle.addEventListener('click', () => {
    circle.style.transform = "scale(1.3)";
    setTimeout(() => {
      circle.style.transform = "scale(1)";
    }, 300);
  });
});
// Animate cards when page loads
    document.addEventListener("DOMContentLoaded", () => {
      const cards = document.querySelectorAll(".card");
      cards.forEach((card, index) => {
        setTimeout(() => {
          card.classList.add("show");
        }, index * 400);
      });
    });
    