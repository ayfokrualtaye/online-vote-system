// ============================================
// APP.JS - Core Application Logic
// ============================================

// --- Page Loader ---
window.addEventListener('load', () => {
  const loader = document.querySelector('.page-loader');
  if (loader) {
    setTimeout(() => loader.classList.add('hidden'), 600);
  }
});

// --- Navbar Scroll Effect ---
const navbar = document.querySelector('.navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
  });
}

// --- Scroll Reveal ---
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 100);
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

// --- Particle Background ---
function initParticles(canvasId = 'particles-canvas') {
  const canvas = document.getElementById(canvasId);
  if (!canvas) return;

  const ctx = canvas.getContext('2d');
  let particles = [];

  const resize = () => {
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;
  };

  resize();
  window.addEventListener('resize', resize);

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x    = Math.random() * canvas.width;
      this.y    = canvas.height + 10;
      this.size = Math.random() * 3 + 1;
      this.speedY = -(Math.random() * 1.5 + 0.5);
      this.speedX = (Math.random() - 0.5) * 0.5;
      this.opacity = Math.random() * 0.5 + 0.1;
      this.color = Math.random() > 0.5 ? '#4facfe' : '#667eea';
    }
    update() {
      this.y += this.speedY;
      this.x += this.speedX;
      if (this.y < -10) this.reset();
    }
    draw() {
      ctx.save();
      ctx.globalAlpha = this.opacity;
      ctx.fillStyle = this.color;
      ctx.beginPath();
      ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();
    }
  }

  for (let i = 0; i < 60; i++) {
    const p = new Particle();
    p.y = Math.random() * canvas.height; // spread initially
    particles.push(p);
  }

  function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => { p.update(); p.draw(); });
    requestAnimationFrame(animate);
  }
  animate();
}

// --- Toast Notifications ---
const Toast = {
  container: null,

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.className = 'toast-container';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 4000) {
    this.init();
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <span class="toast-msg">${message}</span>
      <button class="toast-close" onclick="this.parentElement.remove()">✕</button>
    `;
    this.container.appendChild(toast);
    setTimeout(() => toast.remove(), duration);
  },

  success(msg) { this.show(msg, 'success'); },
  error(msg)   { this.show(msg, 'error'); },
  info(msg)    { this.show(msg, 'info'); }
};

// --- Modal ---
const Modal = {
  open(id) {
    const overlay = document.getElementById(id);
    if (overlay) overlay.classList.add('active');
  },
  close(id) {
    const overlay = document.getElementById(id);
    if (overlay) overlay.classList.remove('active');
  },
  closeAll() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('active'));
  }
};

// Close modal on overlay click
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) Modal.closeAll();
});

// --- Ripple Effect ---
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.btn');
  if (!btn) return;
  const rect = btn.getBoundingClientRect();
  const ripple = document.createElement('span');
  ripple.className = 'ripple';
  const size = Math.max(rect.width, rect.height);
  ripple.style.cssText = `
    width: ${size}px; height: ${size}px;
    left: ${e.clientX - rect.left - size/2}px;
    top: ${e.clientY - rect.top - size/2}px;
  `;
  btn.appendChild(ripple);
  setTimeout(() => ripple.remove(), 600);
});

// --- Animated Counter ---
function animateCounter(el, target, duration = 1500) {
  let start = 0;
  const step = target / (duration / 16);
  const timer = setInterval(() => {
    start += step;
    if (start >= target) { start = target; clearInterval(timer); }
    el.textContent = Math.floor(start).toLocaleString();
  }, 16);
}

// Init counters when visible
const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const el = entry.target;
      const target = parseInt(el.dataset.count || el.textContent);
      animateCounter(el, target);
      counterObserver.unobserve(el);
    }
  });
});

document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

// --- Sidebar Toggle (Mobile) ---
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
if (sidebarToggle && sidebar) {
  sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
}

// --- Active Nav Link ---
const currentPath = window.location.pathname;
document.querySelectorAll('.sidebar-nav a, .navbar-nav a').forEach(link => {
  if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').split('/').pop())) {
    link.classList.add('active');
  }
});

// --- Confirm Dialog ---
function confirmAction(message, callback) {
  if (confirm(message)) callback();
}

// --- Format Date ---
function formatDate(dateStr) {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
}

// Init particles on pages that have the canvas
document.addEventListener('DOMContentLoaded', () => {
  initParticles();
});
