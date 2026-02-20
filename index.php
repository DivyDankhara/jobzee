<?php
/**
 * JobZee - Home Page
 */
require_once __DIR__ . '/includes/auth.php';

define('ROOT_PATH', '');
$pageTitle = 'Find Your Dream Job';

$db = getDB();

// Stats
$totalJobs  = $db->query("SELECT COUNT(*) FROM jobs WHERE status='active' AND deadline >= CURDATE()")->fetchColumn();
$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role='jobseeker'")->fetchColumn();
$totalCo    = $db->query("SELECT COUNT(DISTINCT company) FROM jobs WHERE status='active'")->fetchColumn();

// Latest jobs
$stmt = $db->prepare("SELECT j.*, u.name AS recruiter_name FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE j.status='active' AND j.deadline >= CURDATE() ORDER BY j.created_at DESC LIMIT 6");
$stmt->execute();
$latestJobs = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="container hero-content">
        <h1>Find Your <span>Dream Job</span> Today</h1>
        <p>Browse thousands of job listings from top companies. Your next opportunity is just a search away.</p>
        <form class="search-box" action="jobs.php" method="GET" id="searchForm">
            <input type="text" name="q" id="searchKeyword" placeholder="Job title, keyword, company..." value="<?= sanitize($_GET['q'] ?? '') ?>">
            <input type="text" name="location" placeholder="Location..." value="<?= sanitize($_GET['location'] ?? '') ?>">
            <select name="type" class="form-control">
                <option value="">All Types</option>
                <option value="full-time">Full-Time</option>
                <option value="part-time">Part-Time</option>
                <option value="remote">Remote</option>
                <option value="contract">Contract</option>
                <option value="internship">Internship</option>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Search</button>
        </form>
        <div class="hero-stats">
            <div class="hero-stat"><strong><?= number_format($totalJobs) ?>+</strong><span>Active Jobs</span></div>
            <div class="hero-stat"><strong><?= number_format($totalCo) ?>+</strong><span>Companies</span></div>
            <div class="hero-stat"><strong><?= number_format($totalUsers) ?>+</strong><span>Job Seekers</span></div>
        </div>
    </div>
</section>

<!-- Latest Jobs -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Latest Job Openings</h2>
        <p class="section-subtitle">Fresh opportunities added daily from top employers</p>

        <?php if (empty($latestJobs)): ?>
            <div class="empty-state">
                <div class="empty-icon">📋</div>
                <h3>No jobs available right now</h3>
                <p>Check back soon for new opportunities!</p>
            </div>
        <?php else: ?>
        <div class="jobs-grid">
            <?php foreach ($latestJobs as $job): ?>
            <div class="job-card">
                <div class="job-card-header">
                    <div>
                        <div class="job-company">🏢 <?= sanitize($job['company']) ?></div>
                        <div class="job-title"><a href="job.php?id=<?= $job['id'] ?>"><?= sanitize($job['title']) ?></a></div>
                    </div>
                    <span class="badge <?= jobTypeBadge($job['job_type']) ?>"><?= $job['job_type'] ?></span>
                </div>
                <div class="job-meta">
                    <span>📍 <?= sanitize($job['location']) ?></span>
                    <span>⏰ <?= timeAgo($job['created_at']) ?></span>
                    <span>📅 Deadline: <?= date('M j', strtotime($job['deadline'])) ?></span>
                </div>
                <div class="job-desc"><?= nl2br(sanitize(substr($job['description'], 0, 120))) ?>...</div>
                <div class="job-card-footer">
                    <span class="job-salary"><?= formatSalary($job['salary_min'], $job['salary_max']) ?></span>
                    <a href="job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-primary">View Job</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-8">
            <a href="jobs.php" class="btn btn-outline btn-lg">Browse All Jobs →</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: var(--primary-light); padding: 60px 0;">
    <div class="container text-center">
        <h2 style="font-size:1.75rem; font-weight:800; margin-bottom:12px;">Are You Hiring?</h2>
        <p style="color:var(--gray-600); margin-bottom:28px; max-width:500px; margin-left:auto; margin-right:auto;">Post your job listing and reach thousands of qualified candidates. Simple, fast, and effective.</p>
        <div class="gap-12" style="justify-content:center; flex-wrap:wrap;">
            <a href="auth/register.php?role=recruiter" class="btn btn-primary btn-lg">Post a Job Free</a>
            <a href="jobs.php" class="btn btn-outline btn-lg">Browse Candidates</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
