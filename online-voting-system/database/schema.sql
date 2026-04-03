-- ============================================
-- ONLINE VOTING SYSTEM - FULL DATABASE SCHEMA
-- ============================================

CREATE DATABASE IF NOT EXISTS voting_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE voting_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','voter') DEFAULT 'voter',
    verified TINYINT(1) DEFAULT 0,
    otp_code VARCHAR(10) DEFAULT NULL,
    otp_expires DATETIME DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Elections table
CREATE TABLE IF NOT EXISTS elections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status ENUM('upcoming','active','closed') DEFAULT 'upcoming',
    start_date DATETIME,
    end_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Candidates table
CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    party VARCHAR(100),
    bio TEXT,
    image VARCHAR(255) DEFAULT 'default.png',
    election_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    approval_status ENUM('pending','approved','rejected') DEFAULT 'approved',
    manifesto TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Candidate applications (voters applying to be candidates)
CREATE TABLE IF NOT EXISTS candidate_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    election_id INT NOT NULL,
    party VARCHAR(100),
    bio TEXT,
    manifesto TEXT,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    admin_note TEXT DEFAULT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (user_id, election_id)
);

-- Votes table (anonymous)
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_hash VARCHAR(64) NOT NULL,
    candidate_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (user_hash, election_id),
    FOREIGN KEY (candidate_id) REFERENCES candidates(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- Voter registry (tracks who voted, separate from votes for privacy)
CREATE TABLE IF NOT EXISTS voter_registry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    election_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_voter (user_id, election_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (election_id) REFERENCES elections(id) ON DELETE CASCADE
);

-- Activity logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(255),
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- SEED DATA
-- ============================================

-- Admin user (password: Admin@123)
INSERT INTO users (name, email, password, role, verified) VALUES
('Administrator', 'admin@votesystem.com', '$2y$12$9w1raIB1V7zYhn4.Y7o92e4qZNT7JjSOtakxkvuw7wFCaBbOHrAli', 'admin', 1);

-- Elections
INSERT INTO elections (title, description, status, start_date, end_date) VALUES
('National Election 2026', 'Vote for your preferred presidential candidate for the term 2026-2030. Shape the future of the nation.', 'active', '2026-01-01 00:00:00', '2026-12-31 23:59:59'),
('City Council Election', 'Elect your local city council representatives who will manage city infrastructure, budget, and community programs.', 'active', '2026-03-01 00:00:00', '2026-06-30 23:59:59'),
('Women Leadership Election', 'Empowering women in governance — vote for the next women leadership council members driving equality and progress.', 'active', '2026-04-01 00:00:00', '2026-08-31 23:59:59'),
('Student Union Election', 'Cast your vote for student union president and representatives for the academic year 2026-2027.', 'upcoming', '2026-06-01 00:00:00', '2026-07-15 23:59:59'),
('Community Board Election', 'Vote for community board members who will represent your neighborhood in local governance decisions.', 'upcoming', '2026-07-01 00:00:00', '2026-09-30 23:59:59'),
('Tech Innovation Council', 'Elect the technology and innovation council that will guide digital transformation and smart city initiatives.', 'active', '2026-02-01 00:00:00', '2026-05-31 23:59:59'),
('Environmental Council Election', 'Choose your environmental council representatives committed to climate action and sustainable development.', 'active', '2026-03-15 00:00:00', '2026-07-31 23:59:59');

-- Candidates (Election 1 - National)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Alexandra Rivera', 'Progressive Party', 'Former senator with 15 years of public service experience focused on healthcare and education reform.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop&crop=face', 1),
('Marcus Thompson', 'Liberty Alliance', 'Business leader and community advocate focused on economic growth and job creation.', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop&crop=face', 1),
('Sarah Chen', 'United Front', 'Environmental scientist and policy expert committed to a sustainable and green future.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop&crop=face', 1),
('James Okafor', 'People First', 'Human rights lawyer and activist with a vision for social justice and equality.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face', 1);

-- Candidates (Election 2 - City Council)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Maria Santos', 'City Progress', 'Urban planner with 10 years experience improving city infrastructure and public transport.', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face', 2),
('David Kim', 'Community First', 'Local business owner committed to supporting small businesses and neighborhood development.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face', 2),
('Priya Patel', 'Green City', 'Environmental engineer advocating for clean energy and sustainable city planning.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face', 2);

-- Candidates (Election 3 - Women Leadership)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Dr. Amina Hassan', 'Equality Now', 'Medical doctor and women rights advocate with 20 years of community service.', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face', 3),
('Linda Osei', 'Women United', 'Entrepreneur and mentor who has helped over 500 women start their own businesses.', 'https://images.unsplash.com/photo-1489424731084-a5d8b219a5bb?w=200&h=200&fit=crop&crop=face', 3),
('Rachel Nguyen', 'Future Leaders', 'Tech executive and STEM education advocate empowering the next generation of women leaders.', 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=200&h=200&fit=crop&crop=face', 3);

-- Candidates (Election 4 - Student Union)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Ethan Brooks', 'Student Voice', 'Computer science student and campus activist pushing for better academic resources.', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200&h=200&fit=crop&crop=face', 4),
('Zoe Williams', 'Campus United', 'Psychology student focused on mental health awareness and student welfare programs.', 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&h=200&fit=crop&crop=face', 4),
('Amir Khalil', 'New Generation', 'Engineering student and innovation club president with plans to modernize campus facilities.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop&crop=face', 4);

-- Candidates (Election 5 - Community Board)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Grace Adeyemi', 'Neighborhood First', 'Social worker dedicated to improving community services and youth programs.', 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&h=200&fit=crop&crop=face', 5),
('Tom Reeves', 'Local Roots', 'Retired teacher with 30 years of community involvement and local governance experience.', 'https://images.unsplash.com/photo-1566492031773-4f4e44671857?w=200&h=200&fit=crop&crop=face', 5),
('Nina Castillo', 'Community Forward', 'Urban developer focused on affordable housing and green public spaces.', 'https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=200&h=200&fit=crop&crop=face', 5);

-- Candidates (Election 6 - Tech Innovation)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Ryan Park', 'Digital Future', 'Software engineer and startup founder advocating for digital infrastructure investment.', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face', 6),
('Fatima Al-Rashid', 'Innovation Now', 'AI researcher pushing for ethical technology policies and digital literacy programs.', 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&h=200&fit=crop&crop=face', 6),
('Carlos Mendez', 'Tech for All', 'Cybersecurity expert focused on protecting citizens in the digital age.', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face', 6);

-- Candidates (Election 7 - Environmental Council)
INSERT INTO candidates (name, party, bio, image, election_id) VALUES
('Dr. Lena Fischer', 'Green Earth', 'Climate scientist with 15 years of research on renewable energy and carbon reduction.', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&h=200&fit=crop&crop=face', 7),
('Samuel Obi', 'Planet First', 'Environmental lawyer fighting for stronger pollution regulations and clean water access.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop&crop=face', 7),
('Yuki Tanaka', 'Sustainable Tomorrow', 'Renewable energy entrepreneur who has built solar projects across 10 countries.', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face', 7);
