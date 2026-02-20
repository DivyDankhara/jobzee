-- ============================================================
-- JobZee - Online Job Portal Database
-- ============================================================

CREATE DATABASE IF NOT EXISTS jobzee CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jobzee;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','recruiter','jobseeker') NOT NULL DEFAULT 'jobseeker',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- JOBS TABLE
-- ============================================================
CREATE TABLE jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recruiter_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    company VARCHAR(150) NOT NULL,
    location VARCHAR(150) NOT NULL,
    salary_min DECIMAL(10,2) DEFAULT 0,
    salary_max DECIMAL(10,2) DEFAULT 0,
    job_type ENUM('full-time','part-time','contract','internship','remote') NOT NULL DEFAULT 'full-time',
    description TEXT NOT NULL,
    deadline DATE NOT NULL,
    status ENUM('active','closed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_recruiter (recruiter_id),
    INDEX idx_status (status),
    INDEX idx_location (location),
    INDEX idx_job_type (job_type),
    INDEX idx_deadline (deadline),
    FULLTEXT idx_search (title, description, company),
    CONSTRAINT fk_jobs_recruiter FOREIGN KEY (recruiter_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- APPLICATIONS TABLE
-- ============================================================
CREATE TABLE applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id INT UNSIGNED NOT NULL,
    applicant_id INT UNSIGNED NOT NULL,
    cover_letter TEXT,
    resume_path VARCHAR(500),
    status ENUM('received','reviewing','shortlisted','rejected','hired') NOT NULL DEFAULT 'received',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_job (job_id),
    INDEX idx_applicant (applicant_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_application (job_id, applicant_id),
    CONSTRAINT fk_app_job FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    CONSTRAINT fk_app_applicant FOREIGN KEY (applicant_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin: admin@jobzee.test / Password123!
-- Recruiter: recruiter1@jobzee.test / Password123!
-- Jobseeker: applicant1@jobzee.test / Password123!

INSERT INTO users (name, email, password, role, status) VALUES
('Admin User', 'admin@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('TechCorp HR', 'recruiter1@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('John Smith', 'applicant1@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('GlobalSoft Recruiting', 'recruiter2@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('InnovateTech HR', 'recruiter3@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('DataDriven Inc', 'recruiter4@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('CloudBase Systems', 'recruiter5@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('NextGen Solutions', 'recruiter6@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('Apex Technologies', 'recruiter7@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('FutureWorks Corp', 'recruiter8@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('Digital Ventures', 'recruiter9@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('SmartHire Co', 'recruiter10@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'recruiter', 'active'),
('Sarah Johnson', 'applicant2@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Michael Brown', 'applicant3@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Emily Davis', 'applicant4@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Robert Wilson', 'applicant5@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Jennifer Martinez', 'applicant6@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('David Anderson', 'applicant7@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Lisa Thompson', 'applicant8@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('James Garcia', 'applicant9@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Patricia Miller', 'applicant10@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Charles Jackson', 'applicant11@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Sandra White', 'applicant12@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Mark Harris', 'applicant13@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Betty Clark', 'applicant14@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('William Lewis', 'applicant15@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Dorothy Robinson', 'applicant16@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Joseph Walker', 'applicant17@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Nancy Hall', 'applicant18@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Thomas Allen', 'applicant19@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Margaret Young', 'applicant20@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Paul Hernandez', 'applicant21@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Elizabeth King', 'applicant22@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Kevin Wright', 'applicant23@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Sharon Lopez', 'applicant24@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Brian Hill', 'applicant25@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Karen Scott', 'applicant26@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('George Green', 'applicant27@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Amy Adams', 'applicant28@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Donald Baker', 'applicant29@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active'),
('Melissa Nelson', 'applicant30@jobzee.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jobseeker', 'active');

-- NOTE: The password hash above is for 'password' (Laravel default test hash)
-- For Password123! use the application's registration or update with:
-- UPDATE users SET password = '$2y$10$TKh8H1.PfYi1DOoEu1xVbe7fvL5tP1iLmXLGAEr3dFoK1G8aLXi4e' WHERE email IN ('admin@jobzee.test','recruiter1@jobzee.test','applicant1@jobzee.test');

-- Jobs (recruiter IDs: 2-11)
INSERT INTO jobs (recruiter_id, title, company, location, salary_min, salary_max, job_type, description, deadline) VALUES
(2, 'Senior PHP Developer', 'TechCorp', 'New York, NY', 80000, 120000, 'full-time', 'We are looking for a Senior PHP Developer with 5+ years of experience in building scalable web applications. You will be responsible for developing and maintaining our core product. Requirements: PHP 8+, MySQL, Laravel/Symfony, REST APIs, Git. Nice to have: Redis, Docker, AWS. We offer competitive salary, health benefits, and flexible working hours.', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(2, 'Junior Frontend Developer', 'TechCorp', 'New York, NY', 45000, 65000, 'full-time', 'Join our frontend team and help build beautiful, responsive user interfaces. You will work closely with designers and backend developers. Requirements: HTML5, CSS3, JavaScript (ES6+), React or Vue.js, Git. We offer a great learning environment and mentorship from senior developers.', DATE_ADD(CURDATE(), INTERVAL 25 DAY)),
(4, 'Data Scientist', 'GlobalSoft', 'San Francisco, CA', 100000, 150000, 'full-time', 'Exciting opportunity for a Data Scientist to join our AI/ML team. You will analyze large datasets and build predictive models. Requirements: Python, TensorFlow/PyTorch, SQL, Statistical analysis, Machine Learning. PhD or Masters in relevant field preferred. Remote work options available.', DATE_ADD(CURDATE(), INTERVAL 45 DAY)),
(4, 'DevOps Engineer', 'GlobalSoft', 'Remote', 90000, 130000, 'remote', 'We need a skilled DevOps Engineer to manage our cloud infrastructure. Requirements: AWS/GCP/Azure, Docker, Kubernetes, CI/CD pipelines, Linux, Terraform. Experience with monitoring tools (Prometheus, Grafana) is a plus.', DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
(5, 'Product Manager', 'InnovateTech', 'Austin, TX', 85000, 115000, 'full-time', 'Lead product strategy and roadmap for our SaaS platform. You will work with engineering, design, and business teams. Requirements: 3+ years PM experience, Agile/Scrum, user research, roadmap planning, data-driven decision making.', DATE_ADD(CURDATE(), INTERVAL 35 DAY)),
(5, 'UX/UI Designer', 'InnovateTech', 'Austin, TX', 65000, 90000, 'full-time', 'Design intuitive user experiences for our suite of products. Requirements: Figma, Adobe XD, user research, wireframing, prototyping, HTML/CSS basics. Portfolio required. Remote friendly.', DATE_ADD(CURDATE(), INTERVAL 28 DAY)),
(6, 'Machine Learning Engineer', 'DataDriven Inc', 'Seattle, WA', 110000, 160000, 'full-time', 'Build and deploy ML models at scale. Requirements: Python, scikit-learn, TensorFlow, Spark, SQL, cloud platforms. Experience with NLP or computer vision is a big plus. Competitive equity package offered.', DATE_ADD(CURDATE(), INTERVAL 60 DAY)),
(6, 'Data Analyst', 'DataDriven Inc', 'Seattle, WA', 60000, 85000, 'full-time', 'Analyze business data and provide actionable insights. Requirements: SQL, Python or R, Tableau/PowerBI, Excel, statistical analysis. Strong communication skills required. Entry-level candidates welcome.', DATE_ADD(CURDATE(), INTERVAL 40 DAY)),
(7, 'Cloud Solutions Architect', 'CloudBase Systems', 'Chicago, IL', 130000, 180000, 'full-time', 'Design and implement cloud solutions for enterprise clients. Requirements: AWS/Azure Solutions Architect certification, 7+ years experience, enterprise architecture, security best practices.', DATE_ADD(CURDATE(), INTERVAL 50 DAY)),
(7, 'Backend Node.js Developer', 'CloudBase Systems', 'Chicago, IL', 75000, 105000, 'full-time', 'Develop robust backend APIs and microservices. Requirements: Node.js, Express, MongoDB, PostgreSQL, REST/GraphQL APIs, Docker. TypeScript experience preferred.', DATE_ADD(CURDATE(), INTERVAL 22 DAY)),
(8, 'Mobile App Developer (iOS)', 'NextGen Solutions', 'Boston, MA', 85000, 120000, 'full-time', 'Build and maintain our iOS applications. Requirements: Swift, SwiftUI, Xcode, REST APIs, App Store deployment, Git. Experience with React Native is a plus.', DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
(8, 'Cybersecurity Analyst', 'NextGen Solutions', 'Boston, MA', 80000, 115000, 'full-time', 'Protect our systems and data from security threats. Requirements: Security certifications (CISSP/CEH), penetration testing, SIEM tools, incident response, network security. Secret clearance a plus.', DATE_ADD(CURDATE(), INTERVAL 45 DAY)),
(9, 'Full Stack Python Developer', 'Apex Technologies', 'Denver, CO', 80000, 115000, 'full-time', 'Work on cutting-edge web applications using Python and modern frameworks. Requirements: Python, Django/Flask, React, PostgreSQL, Docker, AWS. Agile development experience required.', DATE_ADD(CURDATE(), INTERVAL 33 DAY)),
(9, 'Marketing Intern', 'Apex Technologies', 'Denver, CO', 15000, 25000, 'internship', 'Join our marketing team for a 6-month internship. You will assist with digital marketing campaigns, social media, content creation, and analytics. Perfect for recent graduates or final-year students.', DATE_ADD(CURDATE(), INTERVAL 15 DAY)),
(10, 'Technical Writer', 'FutureWorks Corp', 'Remote', 55000, 75000, 'remote', 'Create clear and comprehensive technical documentation for our software products. Requirements: Technical writing experience, ability to understand complex technical concepts, Markdown, Git, API documentation.', DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
(10, 'Java Developer', 'FutureWorks Corp', 'Miami, FL', 85000, 120000, 'full-time', 'Develop enterprise-grade Java applications. Requirements: Java 11+, Spring Boot, Hibernate, Microservices, Maven/Gradle, REST APIs, SQL. Experience with Kafka or message queues is a plus.', DATE_ADD(CURDATE(), INTERVAL 38 DAY)),
(11, 'React Native Developer', 'Digital Ventures', 'Los Angeles, CA', 90000, 130000, 'full-time', 'Build cross-platform mobile applications. Requirements: React Native, JavaScript/TypeScript, Redux, iOS/Android deployment, REST APIs. Experience with native modules is a plus.', DATE_ADD(CURDATE(), INTERVAL 27 DAY)),
(11, 'HR Business Partner', 'Digital Ventures', 'Los Angeles, CA', 70000, 95000, 'part-time', 'Support business units with HR strategy and employee relations. Requirements: HR generalist experience, employment law knowledge, HRIS systems, conflict resolution. SHRM certification preferred.', DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
(12, 'Content Marketing Manager', 'SmartHire Co', 'Nashville, TN', 65000, 90000, 'full-time', 'Lead our content strategy and create compelling marketing materials. Requirements: Content strategy, SEO, email marketing, social media, analytics. B2B SaaS experience strongly preferred.', DATE_ADD(CURDATE(), INTERVAL 42 DAY)),
(12, 'QA Engineer', 'SmartHire Co', 'Nashville, TN', 65000, 90000, 'contract', 'Ensure product quality through comprehensive testing. Requirements: Manual and automated testing, Selenium/Cypress, API testing (Postman), JIRA, Agile. ISTQB certification is a plus. 6-month contract with possibility of extension.', DATE_ADD(CURDATE(), INTERVAL 18 DAY));

-- Applications (applicant IDs: 3, 13-42)
INSERT INTO applications (job_id, applicant_id, cover_letter, resume_path, status) VALUES
(1, 3, 'I am very excited to apply for the Senior PHP Developer position. With 6 years of PHP development experience including large-scale ecommerce platforms, I am confident I can contribute significantly to your team.', NULL, 'shortlisted'),
(1, 13, 'Having worked with PHP for 5 years in a startup environment, I bring both technical expertise and startup agility. I am particularly interested in your scalability challenges.', NULL, 'reviewing'),
(1, 14, 'I am a passionate PHP developer with experience in building RESTful APIs and microservices. Your company culture aligns perfectly with my values.', NULL, 'received'),
(2, 15, 'As a junior developer eager to grow, I believe this opportunity is perfect for me. I have completed several React projects during my bootcamp and personal projects.', NULL, 'hired'),
(2, 16, 'I recently graduated with a CS degree and have been building frontend applications in my spare time. I am a quick learner and very motivated.', NULL, 'received'),
(3, 3, 'My data science background includes 3 years of building predictive models for financial services. I hold a Masters in Statistics and am proficient in Python and TensorFlow.', NULL, 'received'),
(3, 17, 'I am a Data Scientist with a PhD in Computer Science. I have published research papers on ML and have industry experience at two startups.', NULL, 'shortlisted'),
(4, 18, 'As a DevOps engineer with 4 years of AWS experience, I have successfully managed infrastructure for companies handling millions of daily requests.', NULL, 'reviewing'),
(4, 19, 'I have been working with Docker and Kubernetes for 3 years and recently obtained my AWS Solutions Architect certification. Excited about this remote opportunity.', NULL, 'received'),
(5, 20, 'I have 4 years of product management experience at SaaS companies, leading cross-functional teams and delivering features that drove 40% revenue growth.', NULL, 'shortlisted'),
(5, 21, 'As an aspiring PM transitioning from software development, I bring a unique technical perspective to product decisions.', NULL, 'rejected'),
(6, 22, 'My UX portfolio includes redesigns for fintech and healthcare applications. I am proficient in Figma and have a strong user research background.', NULL, 'shortlisted'),
(6, 23, 'I am a designer who codes. With 3 years of UI/UX experience and solid HTML/CSS skills, I bridge the gap between design and development.', NULL, 'reviewing'),
(7, 24, 'I have built and deployed ML models at scale for an e-commerce company, reducing recommendation latency by 60% while improving accuracy.', NULL, 'received'),
(7, 25, 'My NLP expertise and experience with transformer models makes me a strong fit for your ML engineering role.', NULL, 'shortlisted'),
(8, 26, 'I am a detail-oriented data analyst with strong SQL skills and experience creating executive dashboards in Tableau.', NULL, 'hired'),
(8, 27, 'Fresh out of college with a Statistics degree, I am eager to put my analytical skills to work in a real-world business environment.', NULL, 'received'),
(9, 28, 'I hold AWS Solutions Architect Professional certification and have 8 years of enterprise cloud architecture experience.', NULL, 'reviewing'),
(10, 29, 'I am a Node.js developer with 4 years of experience building high-performance APIs serving millions of requests. TypeScript is my primary language.', NULL, 'received'),
(10, 30, 'Full stack developer transitioning to backend specialization. Node.js has been my primary technology for the past 2 years.', NULL, 'received'),
(11, 31, 'I have published 5 iOS apps on the App Store and worked at two mobile-first startups. Swift and SwiftUI are my specialties.', NULL, 'shortlisted'),
(12, 32, 'Cybersecurity professional with CISSP certification and 6 years of experience in threat detection and incident response.', NULL, 'reviewing'),
(13, 33, 'I am a Python developer with Django expertise. I love building clean, maintainable code and have experience with all technologies in your stack.', NULL, 'received'),
(14, 34, 'As a recent marketing graduate, I am enthusiastic about this internship opportunity. I manage social media accounts for 3 local businesses.', NULL, 'received'),
(15, 35, 'I have 4 years of technical writing experience for API documentation and developer guides. I am comfortable working remotely and am self-directed.', NULL, 'hired'),
(16, 36, 'Senior Java developer with 7 years of Spring Boot experience. I have built microservices architectures handling 10M+ daily transactions.', NULL, 'reviewing'),
(17, 37, 'React Native developer with apps on both App Store and Google Play. I have experience with native module development as well.', NULL, 'received'),
(18, 38, 'HR professional with 5 years of experience in tech companies. I hold a SHRM-CP certification and specialize in employee development.', NULL, 'received'),
(19, 39, 'Content strategist with B2B SaaS experience. I grew organic traffic by 300% at my previous company through SEO-optimized content.', NULL, 'shortlisted'),
(20, 40, 'QA engineer with Selenium and Cypress expertise. I have 4 years of testing experience in Agile teams and hold ISTQB Advanced certification.', NULL, 'reviewing'),
(1, 41, 'PHP developer with 5 years experience, very interested in this role.', NULL, 'received'),
(2, 42, 'Frontend developer eager to join your team.', NULL, 'received'),
(3, 28, 'My background in statistics and programming makes me an ideal candidate for this role.', NULL, 'rejected'),
(5, 22, 'Product management experience from a design background.', NULL, 'received'),
(7, 30, 'ML experience from side projects and online courses, very motivated.', NULL, 'received'),
(9, 33, 'Cloud architecture interest with strong CS fundamentals.', NULL, 'received'),
(11, 29, 'Mobile development with React Native background.', NULL, 'received'),
(13, 26, 'Python developer with Django experience.', NULL, 'received'),
(15, 31, 'Technical writing alongside mobile development.', NULL, 'received'),
(17, 24, 'React experience that translates well to React Native.', NULL, 'received'),
(19, 37, 'Content creation alongside development experience.', NULL, 'received'),
(6, 40, 'UI/UX design with Figma proficiency.', NULL, 'received'),
(8, 35, 'Analytics from a writing perspective.', NULL, 'received'),
(12, 39, 'Security mindset from content strategy work.', NULL, 'received'),
(14, 41, 'Marketing intern candidate with social media skills.', NULL, 'received'),
(16, 42, 'Java experience from academic and personal projects.', NULL, 'received'),
(18, 38, 'HR background with part-time availability.', NULL, 'reviewing'),
(20, 36, 'QA experience from Java development background.', NULL, 'received'),
(4, 37, 'DevOps interest with infrastructure automation experience.', NULL, 'received'),
(10, 25, 'Backend Node.js with ML background cross-over.', NULL, 'received'),
(6, 34, 'Design intern transitioning from marketing.', NULL, 'received'),
(20, 32, 'QA with security testing overlap.', NULL, 'received');
