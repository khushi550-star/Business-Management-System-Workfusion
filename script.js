// script.js

// ------------------- Signup Form Validation -------------------
const signupForm = document.querySelector('form[action="signup.php"]');
if (signupForm) {
  signupForm.addEventListener('submit', function (e) {
    const name = signupForm.querySelector('input[name="name"]').value.trim();
    const mobile = signupForm.querySelector('input[name="mobile"]').value.trim();
    const mpin = signupForm.querySelector('input[name="mpin"]').value.trim();

    // Simple validation
    if (name === '' || mobile === '' || mpin === '') {
      alert('Please fill all fields');
      e.preventDefault(); // Stop form submission
      return;
    }

    if (mpin.length < 4) {
      alert('Password must be at least 4 characters');
      e.preventDefault();
      return;
    }
  });
}

// ------------------- Login Form Validation -------------------
const loginForm = document.querySelector('form[action="login.php"]');
if (loginForm) {
  loginForm.addEventListener('submit', function (e) {
    const userId = loginForm.querySelector('input[name="userId"]').value.trim();
    const password = loginForm.querySelector('input[name="password"]').value.trim();

    if (userId === '' || password === '') {
      alert('Please fill all fields');
      e.preventDefault();
      return;
    }
  });
}

// ------------------- Toggle Password Visibility -------------------
const passwordFields = document.querySelectorAll('input[type="password"]');
passwordFields.forEach(field => {
  const toggleBtn = document.createElement('span');
  toggleBtn.style.cursor = 'pointer';
  toggleBtn.style.marginLeft = '5px';
  field.parentNode.insertBefore(toggleBtn, field.nextSibling);

  toggleBtn.addEventListener('click', () => {
    field.type = field.type === 'password' ? 'text' : 'password';
  });
});

// ------------------- Hamburger Menu Toggle (if any) -------------------
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });
}
