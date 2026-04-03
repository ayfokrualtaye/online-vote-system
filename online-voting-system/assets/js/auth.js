// ============================================
// AUTH.JS - Login & Registration Logic
// ============================================

document.addEventListener('DOMContentLoaded', () => {

  // --- Password Toggle ---
  document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.querySelector(btn.dataset.target);
      if (!input) return;
      const isText = input.type === 'text';
      input.type = isText ? 'password' : 'text';
      btn.textContent = isText ? '👁️' : '🙈';
    });
  });

  // --- Login Form ---
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = loginForm.querySelector('[type="submit"]');
      const email    = loginForm.querySelector('#email').value.trim();
      const password = loginForm.querySelector('#password').value;
      const csrf     = loginForm.querySelector('[name="csrf_token"]').value;

      if (!email || !password) {
        showFormError('All fields are required.');
        return;
      }

      setLoading(btn, true);

      try {
        const base = window.location.pathname.replace(/\/public\/.*$/, '');
        const res = await fetch(base + '/api/login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ email, password, csrf_token: csrf })
        });
        const data = await res.json();

        if (data.success) {
          showFormSuccess('Login successful! Redirecting...');
          setTimeout(() => { window.location.href = data.redirect; }, 1000);
        } else {
          showFormError(data.message || 'Login failed.');
        }
      } catch {
        showFormError('Network error. Please try again.');
      } finally {
        setLoading(btn, false);
      }
    });
  }

  // --- Register Form ---
  const registerForm = document.getElementById('register-form');
  if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn      = registerForm.querySelector('[type="submit"]');
      const name     = registerForm.querySelector('#name').value.trim();
      const email    = registerForm.querySelector('#email').value.trim();
      const password = registerForm.querySelector('#password').value;
      const confirm  = registerForm.querySelector('#confirm_password').value;
      const csrf     = registerForm.querySelector('[name="csrf_token"]').value;

      if (!name || !email || !password || !confirm) {
        showFormError('All fields are required.'); return;
      }
      if (password.length < 8) {
        showFormError('Password must be at least 8 characters.'); return;
      }
      if (password !== confirm) {
        showFormError('Passwords do not match.'); return;
      }
      if (!isValidEmail(email)) {
        showFormError('Please enter a valid email address.'); return;
      }

      setLoading(btn, true);

      try {
        const base = window.location.pathname.replace(/\/public\/.*$/, '');
        const res = await fetch(base + '/api/register.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email, password, csrf_token: csrf })
        });
        const data = await res.json();

        if (data.success) {
          showFormSuccess(data.message || 'Registration successful!');
          setTimeout(() => { window.location.href = base + '/public/login.php'; }, 1500);
        } else {
          showFormError(data.message || 'Registration failed.');
        }
      } catch {
        showFormError('Network error. Please try again.');
      } finally {
        setLoading(btn, false);
      }
    });

    // Password strength indicator
    const pwInput = registerForm.querySelector('#password');
    if (pwInput) {
      pwInput.addEventListener('input', () => updatePasswordStrength(pwInput.value));
    }
  }

  // --- Helpers ---
  function showFormError(msg) {
    const el = document.getElementById('form-message');
    if (!el) return;
    el.className = 'alert alert-danger';
    el.innerHTML = `⚠️ ${msg}`;
    el.style.display = 'flex';
  }

  function showFormSuccess(msg) {
    const el = document.getElementById('form-message');
    if (!el) return;
    el.className = 'alert alert-success';
    el.innerHTML = `✅ ${msg}`;
    el.style.display = 'flex';
  }

  function setLoading(btn, loading) {
    btn.disabled = loading;
    btn.innerHTML = loading
      ? '<span class="spinner spinner-sm"></span> Please wait...'
      : btn.dataset.text || 'Submit';
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function updatePasswordStrength(password) {
    const bar = document.getElementById('strength-bar');
    const label = document.getElementById('strength-label');
    if (!bar || !label) return;

    let score = 0;
    if (password.length >= 8)  score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const levels = [
      { label: 'Too weak',  color: '#eb3349', width: '25%' },
      { label: 'Weak',      color: '#ff6a00', width: '50%' },
      { label: 'Good',      color: '#4facfe', width: '75%' },
      { label: 'Strong',    color: '#38ef7d', width: '100%' }
    ];

    const level = levels[Math.max(0, score - 1)] || levels[0];
    bar.style.width = password.length ? level.width : '0';
    bar.style.background = level.color;
    label.textContent = password.length ? level.label : '';
    label.style.color = level.color;
  }
});
