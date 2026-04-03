<?php
require_once __DIR__ . '/../core/auth.php';
Auth::startSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us — VoteSecure</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/online-voting-system/assets/css/style.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/animations.css">
  <link rel="stylesheet" href="/online-voting-system/assets/css/themes.css">
  <style>
    .about-hero {
      min-height: 60vh;
      display: flex; align-items: center;
      position: relative; overflow: hidden;
      padding-top: 90px;
    }
    .about-hero-img {
      position: absolute; inset: 0;
      background: url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1600&h=800&fit=crop') center/cover;
      opacity: 0.12;
    }
    .team-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 24px;
    }
    .team-card {
      background: var(--dark-card);
      border: 1px solid var(--dark-border);
      border-radius: var(--radius);
      padding: 28px 20px;
      text-align: center;
      transition: var(--transition);
    }
    .team-card:hover {
      transform: translateY(-6px);
      border-color: rgba(79,172,254,0.3);
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .team-card img {
      width: 80px; height: 80px;
      border-radius: 50%; object-fit: cover;
      border: 3px solid rgba(79,172,254,0.4);
      margin-bottom: 14px;
    }
    .team-card h4 { font-size: 1rem; margin-bottom: 4px; }
    .team-card p  { font-size: 0.8rem; color: var(--primary-start); }
    .value-card {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: var(--radius);
      padding: 28px;
      backdrop-filter: blur(20px);
      transition: var(--transition);
    }
    .value-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-glow); }
    .value-icon {
      width: 56px; height: 56px;
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .value-icon img { width: 100%; height: 100%; object-fit: cover; }
    .timeline { position: relative; padding-left: 32px; }
    .timeline::before {
      content: '';
      position: absolute; left: 8px; top: 0; bottom: 0;
      width: 2px;
      background: linear-gradient(180deg, #4facfe, #667eea, #ff6a00);
    }
    .timeline-item { position: relative; margin-bottom: 32px; }
    .timeline-dot {
      position: absolute; left: -28px; top: 4px;
      width: 14px; height: 14px;
      border-radius: 50%;
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      border: 2px solid var(--dark-bg);
    }
    .timeline-year {
      font-size: 0.75rem; font-weight: 700;
      color: var(--primary-start);
      text-transform: uppercase; letter-spacing: 1px;
      margin-bottom: 4px;
    }
    .timeline-title { font-size: 1rem; font-weight: 700; margin-bottom: 4px; }
    .timeline-desc  { font-size: 0.85rem; color: var(--text-muted); }
  </style>
</head>
<body style="background:#0f172a;min-height:100vh;">
<canvas id="particles-canvas"></canvas>

<!-- Navbar -->
<nav class="navbar">
  <a href="/online-voting-system/public/index.php" class="navbar-brand">
    <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=80&h=80&fit=crop"
         style="width:36px;height:36px;border-radius:10px;object-fit:cover;" alt="">
    VoteSecure
  </a>
  <ul class="navbar-nav">
    <li><a href="/online-voting-system/public/index.php">Home</a></li>
    <li><a href="/online-voting-system/public/elections.php">Elections</a></li>
    <li><a href="/online-voting-system/public/about.php" class="active">About Us</a></li>
    <?php if (Auth::isLoggedIn()): ?>
    <li><a href="<?= Auth::isAdmin() ? '/online-voting-system/views/admin/dashboard.php' : '/online-voting-system/views/voter/dashboard.php' ?>" class="btn btn-primary btn-sm">Dashboard</a></li>
    <?php else: ?>
    <li><a href="/online-voting-system/public/login.php" class="btn btn-outline btn-sm">Login</a></li>
    <li><a href="/online-voting-system/public/register.php" class="btn btn-primary btn-sm">Get Started</a></li>
    <?php endif; ?>
  </ul>
</nav>

<!-- Hero -->
<div class="about-hero">
  <div class="about-hero-img"></div>
  <div class="container" style="position:relative;z-index:1;">
    <div style="max-width:640px;" class="animate-fade-in">
      <p style="color:var(--primary-start);font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;">Our Mission</p>
      <h1 style="margin-bottom:20px;">Empowering <span class="gradient-text">Democracy</span><br>Through Technology</h1>
      <p style="font-size:1.1rem;color:var(--text-muted);line-height:1.8;">
        VoteSecure was built on the belief that every voice matters. We combine cutting-edge security
        with an intuitive experience to make democratic participation accessible to everyone.
      </p>
    </div>
  </div>
</div>

<!-- Stats Banner -->
<div style="background:var(--dark-card);border-top:1px solid var(--dark-border);border-bottom:1px solid var(--dark-border);padding:40px 0;">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;text-align:center;">
      <?php
      $stats = [
        ['https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=60&h=60&fit=crop', '50K+', 'Registered Voters'],
        ['https://images.unsplash.com/photo-1494172961521-33799ddd43a5?w=60&h=60&fit=crop', '120+', 'Elections Held'],
        ['https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=60&h=60&fit=crop', '99.9%', 'System Uptime'],
        ['https://images.unsplash.com/photo-1563986768609-322da13575f3?w=60&h=60&fit=crop', '0', 'Security Breaches'],
      ];
      foreach ($stats as $s): ?>
      <div class="reveal">
        <img src="<?= $s[0] ?>" style="width:48px;height:48px;border-radius:12px;object-fit:cover;margin-bottom:12px;" alt="">
        <div style="font-size:2rem;font-weight:900;" class="gradient-text"><?= $s[1] ?></div>
        <div style="font-size:0.85rem;color:var(--text-muted);"><?= $s[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Mission & Vision -->
<section class="section">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;" class="reveal">
      <div>
        <img src="https://images.unsplash.com/photo-1540910419892-4a36d2c3266c?w=700&h=500&fit=crop"
             style="width:100%;border-radius:20px;box-shadow:0 30px 60px rgba(0,0,0,0.4);" alt="Mission">
      </div>
      <div>
        <p style="color:var(--primary-start);font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;">Who We Are</p>
        <h2 style="margin-bottom:20px;">Built for <span class="gradient-text-purple">Transparent</span> Democracy</h2>
        <p style="margin-bottom:16px;line-height:1.8;">VoteSecure is a next-generation online voting platform designed to make elections secure, transparent, and accessible. We believe that technology should strengthen democracy, not complicate it.</p>
        <p style="line-height:1.8;">Our platform uses military-grade encryption, anonymous vote storage, and real-time audit trails to ensure every election is fair, verifiable, and tamper-proof.</p>
        <div style="display:flex;gap:16px;margin-top:28px;">
          <a href="/online-voting-system/public/register.php" class="btn btn-primary">Join Now</a>
          <a href="/online-voting-system/public/elections.php" class="btn btn-outline">View Elections</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Core Values -->
<section class="section" style="background:rgba(255,255,255,0.02);">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <h2>Our Core <span class="gradient-text">Values</span></h2>
      <p>The principles that guide everything we build</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;">
      <?php
      $values = [
        ['https://images.unsplash.com/photo-1563986768609-322da13575f3?w=120&h=120&fit=crop', 'Security First', 'Every feature is built with security as the foundation, not an afterthought.'],
        ['https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=120&h=120&fit=crop', 'Full Transparency', 'Open audit trails and verifiable results build trust in every election.'],
        ['https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=120&h=120&fit=crop', 'Inclusivity', 'Designed for everyone — accessible on any device, in any location.'],
        ['https://images.unsplash.com/photo-1551836022-d5d88e9218df?w=120&h=120&fit=crop', 'Privacy', 'Your vote is anonymous. We separate identity from choice by design.'],
      ];
      foreach ($values as $v): ?>
      <div class="value-card reveal">
        <div class="value-icon"><img src="<?= $v[0] ?>" alt="<?= $v[1] ?>"></div>
        <h3 style="font-size:1rem;margin-bottom:8px;"><?= $v[1] ?></h3>
        <p style="font-size:0.85rem;"><?= $v[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Team -->
<section class="section">
  <div class="container">
    <div class="text-center mb-4 reveal">
      <h2>Meet the <span class="gradient-text">Team</span></h2>
      <p>The people behind VoteSecure</p>
    </div>
    <div class="team-grid">
      <?php
      $team = [
        ['https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop&crop=face', 'James Carter', 'CEO & Co-Founder'],
        ['https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop&crop=face', 'Priya Sharma', 'CTO & Security Lead'],
        ['https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop&crop=face', 'Sarah Chen', 'Head of Design'],
        ['https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face', 'Marcus Lee', 'Lead Developer'],
        ['https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face', 'Maria Santos', 'Product Manager'],
        ['https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face', 'David Kim', 'DevOps Engineer'],
      ];
      foreach ($team as $t): ?>
      <div class="team-card reveal">
        <img src="<?= $t[0] ?>" alt="<?= $t[1] ?>">
        <h4><?= $t[1] ?></h4>
        <p><?= $t[2] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Timeline -->
<section class="section" style="background:rgba(255,255,255,0.02);">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:start;" class="reveal">
      <div>
        <p style="color:var(--primary-start);font-size:0.85rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;margin-bottom:12px;">Our Journey</p>
        <h2 style="margin-bottom:32px;">How We <span class="gradient-text">Got Here</span></h2>
        <div class="timeline">
          <?php
          $milestones = [
            ['2020', 'Founded', 'VoteSecure was founded with a mission to modernize democratic participation.'],
            ['2021', 'First Election', 'Successfully ran our first digital election with 500 participants.'],
            ['2022', 'Security Certified', 'Achieved ISO 27001 security certification and passed independent audits.'],
            ['2023', 'Scale Up', 'Expanded to support 10,000+ concurrent voters across multiple elections.'],
            ['2024', 'Global Reach', 'Deployed in 15 countries supporting local and national elections.'],
            ['2026', 'Today', 'Powering thousands of elections with zero security incidents.'],
          ];
          foreach ($milestones as $m): ?>
          <div class="timeline-item">
            <div class="timeline-dot"></div>
            <div class="timeline-year"><?= $m[0] ?></div>
            <div class="timeline-title"><?= $m[1] ?></div>
            <div class="timeline-desc"><?= $m[2] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?w=700&h=600&fit=crop"
             style="width:100%;border-radius:20px;box-shadow:0 30px 60px rgba(0,0,0,0.4);position:sticky;top:100px;" alt="Team">
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container text-center reveal">
    <div style="position:relative;border-radius:24px;overflow:hidden;max-width:800px;margin:0 auto;">
      <img src="https://images.unsplash.com/photo-1494172961521-33799ddd43a5?w=1200&h=350&fit=crop"
           style="width:100%;height:280px;object-fit:cover;" alt="">
      <div style="position:absolute;inset:0;background:rgba(15,23,42,0.88);display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;">
        <h2 style="margin-bottom:12px;">Ready to <span class="gradient-text">Make a Difference?</span></h2>
        <p style="margin-bottom:24px;color:var(--text-muted);">Join thousands of voters already using VoteSecure.</p>
        <div style="display:flex;gap:16px;">
          <a href="/online-voting-system/public/register.php" class="btn btn-primary btn-lg">Get Started Free</a>
          <a href="/online-voting-system/public/elections.php" class="btn btn-outline btn-lg">Browse Elections</a>
        </div>
      </div>
    </div>
  </div>
</section>

<footer>
  <div class="container">
    <div class="footer-bottom" style="border-top:1px solid var(--dark-border);padding-top:24px;">
      <span>© <?= date('Y') ?> VoteSecure. All rights reserved.</span>
      <span>🔒 Secured with end-to-end encryption</span>
    </div>
  </div>
</footer>

<script src="/online-voting-system/assets/js/app.js"></script>
</body>
</html>
