# VoteSecure — Online Voting System

A full-stack secure online voting system built with PHP, MySQL, and vanilla JS. Features a modern dark UI with glassmorphism design, real-time results, and production-level security.

---

## Tech Stack

- **Frontend:** HTML5, CSS3 (Grid, Flexbox, Animations), JavaScript (ES6, Fetch API)
- **Backend:** PHP 8+ (OOP, MVC-inspired structure)
- **Database:** MySQL 5.7+
- **Charts:** Chart.js (CDN)
- **Server:** Apache (XAMPP recommended)

---

## Project Structure

```
online-voting-system/
├── api/                    # JSON API endpoints
│   ├── login.php
│   ├── register.php
│   ├── vote.php
│   └── results.php
├── assets/
│   ├── css/
│   │   ├── style.css       # Main design system
│   │   ├── animations.css  # Keyframes & effects
│   │   └── themes.css      # Page-specific themes
│   ├── js/
│   │   ├── app.js          # Core: particles, modals, toasts
│   │   ├── auth.js         # Login/register logic
│   │   └── vote.js         # Voting & charts logic
│   └── images/
│       └── candidates/     # Candidate photo uploads
├── core/
│   ├── config.php          # DB credentials & constants
│   ├── database.php        # Singleton DB class
│   ├── security.php        # Hashing, CSRF, rate limiting
│   └── auth.php            # Session & role management
├── database/
│   └── schema.sql          # Full DB schema + seed data
├── models/
│   ├── User.php
│   ├── Election.php
│   ├── Candidate.php
│   └── Vote.php
├── public/                 # Entry points
│   ├── index.php           # Homepage
│   ├── login.php
│   ├── register.php
│   ├── logout.php
│   └── results.php
└── views/
    ├── admin/              # Admin panel pages
    │   ├── dashboard.php
    │   ├── elections.php
    │   ├── candidates.php
    │   ├── users.php
    │   ├── results.php
    │   └── logs.php
    └── voter/              # Voter pages
        ├── dashboard.php
        └── vote.php
```

---

## Setup

### Requirements

- XAMPP (or any Apache + PHP 8+ + MySQL stack)
- PHP extensions: `mysqli`, `mbstring`, `openssl`

### Installation

1. Clone or copy the project into your XAMPP `htdocs` folder:
   ```
   C:/xampp/htdocs/online-voting-system/
   ```

2. Start **Apache** and **MySQL** in XAMPP Control Panel.

3. Open [phpMyAdmin](http://localhost/phpmyadmin) and import the database:
   ```
   online-voting-system/database/schema.sql
   ```

4. Update DB credentials in `core/config.php` if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'voting_system');
   ```

5. Visit the app:
   ```
   http://localhost/online-voting-system/public/
   ```

---

## Default Credentials

| Role  | Email                    | Password   |
|-------|--------------------------|------------|
| Admin | admin@votesystem.com     | Admin@123  |

> Register new voter accounts from the registration page.

---

## Features

### Voter
- Register & login with secure sessions
- View active elections on dashboard
- Select a candidate and cast a vote (with confirmation modal)
- One vote per election enforced at DB level
- View real-time results with charts

### Admin
- Dashboard with live stats (voters, elections, votes, candidates)
- Create / edit / delete elections with status control
- Add candidates with photo upload per election
- Manage and delete voter accounts
- View results with doughnut and bar charts
- Full activity audit log

---

## Security

| Feature | Implementation |
|---|---|
| Password hashing | bcrypt (cost 12) |
| CSRF protection | Token per session, validated on every POST |
| SQL injection | Prepared statements throughout |
| Session security | HttpOnly, SameSite=Strict cookies |
| Rate limiting | Session-based (5 login attempts / 5 min) |
| Vote anonymity | User hash (SHA-256) stored instead of user ID |
| Duplicate vote prevention | Unique constraint on `voter_registry` table |
| Input sanitization | `htmlspecialchars` + `strip_tags` on all input |
| Role-based access | Admin/voter separation enforced server-side |

---

## UI Design

- Dark theme base: `#0f172a`
- Primary gradient: `#4facfe → #00f2fe`
- Secondary gradient: `#667eea → #764ba2`
- Accent: `#ff6a00`
- Glassmorphism cards with `backdrop-filter: blur`
- Animated particle background
- Scroll-reveal animations
- Responsive — works on mobile, tablet, desktop

---

## Production Checklist

Before deploying to a live server:

- [ ] Set `error_reporting(0)` and `display_errors = 0` in `config.php`
- [ ] Enable HTTPS and set `'secure' => true` in session cookie params (`core/auth.php`)
- [ ] Change `HASH_SALT` in `config.php` to a strong random value
- [ ] Configure real email credentials for OTP/notifications
- [ ] Set strong MySQL user password (don't use root)
- [ ] Restrict `logs/` and `core/` directories via `.htaccess`
- [ ] Enable regular database backups

---

## License

MIT — free to use and modify for personal or commercial projects.
