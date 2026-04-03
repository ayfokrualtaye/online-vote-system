<?php
require_once __DIR__ . '/../core/auth.php';
Auth::startSession();
if (Auth::isLoggedIn()) {
    $redirect = Auth::isAdmin() ? '/online-voting-system/views/admin/dashboard.php' : '/online-voting-system/views/voter/dashboard.php';
    header("Location: $redirect"); exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VoteSecure — Secure Online Voting System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
  <style>
    /* ---- Animated hero title ---- */
    @keyframes colorCycle {
      0%   { color: #4facfe; text-shadow: 0 0 20px rgba(79,172,254,0.6); }
      20%  { color: #00f2fe; text-shadow: 0 0 20px rgba(0,242,254,0.6); }
      40%  { color: #667eea; text-shadow: 0 0 20px rgba(102,126,234,0.6); }
      60%  { color: #ff6a00; text-shadow: 0 0 20px rgba(255,106,0,0.6); }
      80%  { color: #38ef7d; text-shadow: 0 0 20px rgba(56,239,125,0.6); }
      100% { color: #4facfe; text-shadow: 0 0 20px rgba(79,172,254,0.6); }
    }
    @keyframes wordEntrance {
      0%   { opacity: 0; transform: translateY(30px) scale(0.8); }
      100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animated-title {
      display: flex;
      flex-wrap: wrap;
      gap: 0 16px;
      line-height: 1.15;
    }
    .animated-title .word {
      display: inline-block;
      opacity: 0;
      animation:
        wordEntrance 0.6s cubic-bezier(0.175,0.885,0.32,1.275) forwards,
        colorCycle 4s ease-in-out infinite;
      animation-delay: calc(var(--i) * 0.15s), calc(var(--i) * 0.15s + 0.8s);
      font-weight: 900;
      letter-spacing: -1px;
    }
    /* ---- Hero image layout ---- */
    .hero-split {
      display: grid;
      grid-template-columns: 1fr 1fr;
      align-items: center;
      gap: 60px;
      min-height: 100vh;
      padding-top: 90px;
    }
    .hero-image-wrap {
      position: relative;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 40px 80px rgba(0,0,0,0.5);
      animation: fadeInRight 0.8s ease forwards;
    }
    .hero-image-wrap img {
      width: 100%;
      height: 520px;
      object-fit: cover;
      display: block;
    }
    .hero-image-wrap::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(79,172,254,0.15), rgba(102,126,234,0.1));
      pointer-events: none;
    }
    .hero-image-badge {
      position: absolute;
      bottom: 24px; left: 24px;
      background: rgba(15,23,42,0.85);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(79,172,254,0.3);
      border-radius: 14px;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      z-index: 2;
    }
    .hero-image-badge img {
      width: 40px; height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #4facfe;
    }
    /* ---- Feature cards with images ---- */
    .feature-img-card {
      background: var(--dark-card);
      border: 1px solid var(--dark-border);
      border-radius: var(--radius);
      overflow: hidden;
      transition: var(--transition);
    }
    .feature-img-card:hover {
      transform: translateY(-6px);
      border-color: rgba(79,172,254,0.3);
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .feature-img-card img {
      width: 100%;
      height: 180px;
      object-fit: cover;
      display: block;
    }
    .feature-img-body {
      padding: 20px;
    }
    /* ---- Steps with images ---- */
    .step-img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 16px;
      border: 1px solid var(--dark-border);
    }
    /* ---- Candidate preview ---- */
    .candidate-preview {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-top: 40px;
    }
    .candidate-preview-card {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius);
      padding: 20px;
      text-align: center;
      backdrop-filter: blur(20px);
      transition: var(--transition);
    }
    .candidate-preview-card:hover { transform: translateY(-4px); }
    .candidate-preview-card img {
      width: 70px; height: 70px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid rgba(79,172,254,0.4);
      margin-bottom: 10px;
    }
    @media (max-width: 900px) {
      .hero-split { grid-template-columns: 1fr; }
      .hero-image-wrap { display: none; }
      .candidate-preview { grid-template-columns: 1fr 1fr; }
    }
  </style>
</head>
<body class="theme-home bg-animated">

<!-- Page Loader -->
<div class="page-loader">
  <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=60&h=60&fit=crop&crop=center"
       style="width:48px;height:48px;border-radius:12px;margin-bottom:12px;" alt="">
  <div class="loader-logo">VoteSecure</div>
  <div class="spinner"></div>
</div>

<canvas id="particles-canvas"></canvas>

<!-- Navbar -->
<nav class="navbar">
  <a href="index.php" class="navbar-brand">
    <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=80&h=80&fit=crop&crop=center"
         style="width:36px;height:36px;border-radius:10px;object-fit:cover;" alt="VoteSecure">
    VoteSecure
  </a>
  <ul class="navbar-nav">
    <li><a href="#features">Features</a></li>
    <li><a href="#how-it-works">How It Works</a></li>
    <li><a href="#candidates">Candidates</a></li>
    <li><a href="/online-voting-system/public/elections.php">Elections</a></li>
    <li><a href="/online-voting-system/public/about.php">About Us</a></li>
    <li><a href="login.php" class="btn btn-outline btn-sm">Login</a></li>
    <li><a href="register.php" class="btn btn-primary btn-sm">Get Started</a></li>
  </ul>
</nav>

<!-- Hero Section -->
<section class="hero" style="overflow:visible;">
  <div class="hero-bg"></div>
  <div class="orb-1 hero-orb"></div>
  <div class="orb-2 hero-orb"></div>

  <div class="container">
    <div class="hero-split">
      <!-- Left: Text -->
      <div class="hero-content animate-fade-in">
        <p class="hero-subtitle">🔐 Trusted & Transparent</p>
        <h1 class="hero-title">
          <span class="animated-title">
            <span class="word" style="--i:0">Secure</span>
            <span class="word" style="--i:1">Online</span>
            <span class="word" style="--i:2">Voting</span>
            <span class="word" style="--i:3">System</span>
          </span>
        </h1>
        <p class="hero-desc">
          Cast your vote with confidence. Our platform ensures every vote is counted,
          every voice is heard, and every election is transparent.
        </p>
        <div class="hero-buttons">
          <a href="register.php" class="btn btn-primary btn-lg">Start Voting</a>
          <a href="#features" class="btn btn-outline btn-lg">Learn More</a>
        </div>

        <!-- Stats -->
        <div class="stats-grid mt-4" style="grid-template-columns:repeat(3,1fr);">
          <div class="stat-card blue" style="padding:16px;">
            <div class="stat-icon blue" style="width:40px;height:40px;font-size:1rem;">
              <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=40&h=40&fit=crop&crop=faces" style="width:100%;height:100%;border-radius:8px;object-fit:cover;" alt="">
            </div>
            <div class="stat-info">
              <h3 data-count="12500" style="font-size:1.4rem;">0</h3>
              <p style="font-size:0.75rem;">Voters</p>
            </div>
          </div>
          <div class="stat-card purple" style="padding:16px;">
            <div class="stat-icon purple" style="width:40px;height:40px;font-size:1rem;">
              <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=40&h=40&fit=crop" style="width:100%;height:100%;border-radius:8px;object-fit:cover;" alt="">
            </div>
            <div class="stat-info">
              <h3 data-count="8200" style="font-size:1.4rem;">0</h3>
              <p style="font-size:0.75rem;">Votes Cast</p>
            </div>
          </div>
          <div class="stat-card green" style="padding:16px;">
            <div class="stat-icon green" style="width:40px;height:40px;font-size:1rem;">
              <img src="https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=40&h=40&fit=crop" style="width:100%;height:100%;border-radius:8px;object-fit:cover;" alt="">
            </div>
            <div class="stat-info">
              <h3 data-count="99" style="font-size:1.4rem;">0</h3>
              <p style="font-size:0.75rem;">% Uptime</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Image -->
      <div class="hero-image-wrap animate-fade-in delay-2">
        <img src="https://images.unsplash.com/photo-1494172961521-33799ddd43a5?w=800&h=600&fit=crop&crop=center"
             alt="Voting">
        <div class="hero-image-badge">
          <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=80&h=80&fit=crop&crop=face" alt="">
          <div>
            <div style="font-size:0.85rem;font-weight:700;color:var(--text-primary);">Sarah voted</div>
            <div style="font-size:0.75rem;color:var(--primary-start);">2 minutes ago ✓</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <h2>Why Choose <span class="gradient-text">VoteSecure?</span></h2>
      <p>Built with security and transparency at its core</p>
    </div>
    <div class="features-grid">
      <?php
      $features = [
        ['https://images.unsplash.com/photo-1563986768609-322da13575f3?w=400&h=200&fit=crop', 'End-to-End Security', 'Military-grade encryption protects every vote from submission to counting.', 'rgba(79,172,254,0.8)'],
        ['https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=400&h=200&fit=crop', 'One Person, One Vote', 'Advanced duplicate detection ensures every voter can only vote once.', 'rgba(102,126,234,0.8)'],
        ['https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400&h=200&fit=crop', 'Real-Time Results', 'Watch results update live as votes are counted with full transparency.', 'rgba(17,153,142,0.8)'],
        ['https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400&h=200&fit=crop', 'Mobile Friendly', 'Vote from any device — desktop, tablet, or smartphone.', 'rgba(255,106,0,0.8)'],
        ['https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=400&h=200&fit=crop', 'Full Audit Trail', 'Every action is logged for complete accountability and verification.', 'rgba(238,9,121,0.8)'],
        ['https://images.unsplash.com/photo-1558494949-ef010cbdcc31?w=400&h=200&fit=crop', 'Lightning Fast', 'Optimized infrastructure handles thousands of concurrent voters.', 'rgba(79,172,254,0.8)'],
      ];
      foreach ($features as $i => $f): ?>
      <div class="feature-img-card reveal delay-<?= ($i % 5) + 1 ?>">
        <div style="position:relative;overflow:hidden;">
          <img src="<?= $f[0] ?>" alt="<?= $f[1] ?>" style="width:100%;height:180px;object-fit:cover;display:block;transition:transform 0.4s ease;">
          <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(15,23,42,0.8),transparent);"></div>
        </div>
        <div class="feature-img-body">
          <h3 style="margin-bottom:8px;"><?= $f[1] ?></h3>
          <p style="font-size:0.9rem;"><?= $f[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Candidate Preview -->
<section class="section" id="candidates" style="background:rgba(255,255,255,0.02);">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <h2>Meet the <span class="gradient-text">Candidates</span></h2>
      <p>Presidential Election 2026 — Your vote decides the future</p>
    </div>
    <div class="candidate-preview reveal">
      <?php
      $candidates = [
        ['https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop&crop=face', 'Alexandra Rivera', 'Progressive Party'],
        ['https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop&crop=face', 'Marcus Thompson', 'Liberty Alliance'],
        ['https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop&crop=face', 'Sarah Chen', 'United Front'],
      ];
      foreach ($candidates as $c): ?>
      <div class="candidate-preview-card">
        <img src="<?= $c[0] ?>" alt="<?= $c[1] ?>">
        <div style="font-weight:700;font-size:1rem;margin-bottom:4px;"><?= $c[1] ?></div>
        <div style="font-size:0.8rem;color:var(--primary-start);"><?= $c[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 reveal">
      <a href="register.php" class="btn btn-primary btn-lg">Register to Vote</a>
    </div>
  </div>
</section>

<!-- How It Works -->
<section class="section" id="how-it-works">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <h2>How It <span class="gradient-text-purple">Works</span></h2>
      <p>Simple, secure, and transparent in 3 steps</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:32px;max-width:960px;margin:0 auto;">
      <?php
      $steps = [
        ['https://images.unsplash.com/photo-1586281380349-632531db7ed4?w=600&h=320&fit=crop', '01', 'Register', 'Create your secure account with email verification in under a minute.'],
        ['https://images.unsplash.com/photo-1614064641938-3bbee52942c7?w=600&h=320&fit=crop', '02', 'Authenticate', 'Log in securely. Your identity is verified before you can vote.'],
        ['https://images.unsplash.com/photo-1494172961521-33799ddd43a5?w=600&h=320&fit=crop', '03', 'Vote', 'Select your candidate and cast your anonymous, encrypted vote.'],
      ];
      foreach ($steps as $step): ?>
      <div class="card reveal" style="padding:0;overflow:hidden;">
        <img src="<?= $step[0] ?>" alt="<?= $step[2] ?>" class="step-img" style="border-radius:0;margin:0;height:180px;">
        <div style="padding:24px;">
          <div style="font-size:2.5rem;font-weight:900;background:linear-gradient(135deg,#4facfe,#00f2fe);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:8px;"><?= $step[1] ?></div>
          <h3 style="margin-bottom:8px;"><?= $step[2] ?></h3>
          <p><?= $step[3] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA Section -->
<section class="section">
  <div class="container reveal">
    <div style="position:relative;border-radius:24px;overflow:hidden;max-width:900px;margin:0 auto;">
      <img src="https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1200&h=400&fit=crop&crop=center"
           style="width:100%;height:320px;object-fit:cover;display:block;" alt="Vote">
      <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,23,42,0.92),rgba(79,172,254,0.3));display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:40px;">
        <h2 style="margin-bottom:12px;">Ready to <span class="gradient-text">Vote?</span></h2>
        <p style="margin-bottom:28px;max-width:480px;">Join thousands of voters who trust VoteSecure for fair and transparent elections.</p>
        <div class="hero-buttons" style="justify-content:center;">
          <a href="register.php" class="btn btn-primary btn-lg">Create Account</a>
          <a href="login.php" class="btn btn-outline btn-lg">Sign In</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=80&h=80&fit=crop"
               style="width:36px;height:36px;border-radius:10px;object-fit:cover;" alt="">
          <span style="font-size:1.2rem;font-weight:800;">VoteSecure</span>
        </div>
        <p>A secure, transparent, and accessible online voting platform for modern democracy.</p>
      </div>
      <div class="footer-links">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="login.php">Login</a></li>
          <li><a href="register.php">Register</a></li>
          <li><a href="results.php">Results</a></li>
        </ul>
      </div>
      <div class="footer-links">
        <h4>Security</h4>
        <ul>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Use</a></li>
          <li><a href="#">Security Info</a></li>
          <li><a href="#">Contact</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> VoteSecure. All rights reserved.</span>
      <span>🔒 Secured with end-to-end encryption</span>
    </div>
  </div>
</footer>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
