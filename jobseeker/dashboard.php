<?php
/**
 * JobZee - Jobseeker Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('jobseeker');

$db  = getDB();
$uid = userId();

// Stats
$total      = $db->prepare("SELECT COUNT(*) FROM applications WHERE applicant_id = ?"); $total->execute([$uid]); $total = $total->fetchColumn();
$shortlisted= $db->prepare("SELECT COUNT(*) FROM applications WHERE applicant_id = ? AND status='shortlisted'"); $shortlisted->execute([$uid]); $shortlisted = $shortlisted->fetchColumn();
$hired      = $db->prepare("SELECT COUNT(*) FROM applications WHERE applicant_id = ? AND status='hired'"); $hired->execute([$uid]); $hired = $hired->fetchColumn();
$pending    = $db->prepare("SELECT COUNT(*) FROM applications WHERE applicant_id = ? AND status IN ('received','reviewing')"); $pending->execute([$uid]); $pending = $pending->fetchColumn();

// Recent applications
$stmt = $db->prepare("SELECT a.*, j.title AS job_title, j.company, j.location, j.job_type, j.salary_min, j.salary_max FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.applicant_id = ? ORDER BY a.created_at DESC LIMIT 5");
$stmt->execute([$uid]);
$recentApps = $stmt->fetchAll();

// Recommended jobs (newest active, not already applied)
$stmt2 = $db->prepare("SELECT j.*, u.name AS recruiter_name FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE j.status='active' AND j.deadline >= CURDATE() AND j.id NOT IN (SELECT job_id FROM applications WHERE applicant_id = ?) ORDER BY j.created_at DESC LIMIT 4");
$stmt2->execute([$uid]);
$recommended = $stmt2->fetchAll();

$pageTitle = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1 class="dashboard-title">Welcome, <?= sanitize(userName()) ?>!</h1>
        <p class="dashboard-subtitle">Track your applications and discover new opportunities</p>
    </div>
</div>

<div class="container dashboard-content">
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon blue">📨</div><div class="stat-info"><strong><?= $total ?></strong><span>Total Applied</span></div></div>
        <div class="stat-card"><div class="stat-icon purple">⭐</div><div class="stat-info"><strong><?= $shortlisted ?></strong><span>Shortlisted</span></div></div>
        <div class="stat-card"><div class="stat-icon orange">⏳</div><div class="stat-info"><strong><?= $pending ?></strong><span>In Review</span></div></div>
        <div class="stat-card"><div class="stat-icon green">🎉</div><div class="stat-info"><strong><?= $hired ?></strong><span>Hired</span></div></div>
    </div>

    <div style="display:flex; gap:12px; margin-bottom:32px; flex-wrap:wrap;">
        <a href="../jobs.php" class="btn btn-primary">🔍 Browse Jobs</a>
        <a href="my_applications.php" class="btn btn-outline">My Applications</a>
    </div>

    <!-- Recent Applications -->
    <div class="section-header"><h2>Recent Applications</h2><a href="my_applications.php" class="btn btn-sm btn-outline">View All</a></div>
    <div class="table-container mb-8">
        <?php if (empty($recentApps)): ?>
            <div class="empty-state"><div class="empty-icon">📋</div><h3>No applications yet</h3><p><a href="../jobs.php">Browse jobs</a> and apply today!</p></div>
        <?php else: ?>
        <table>
            <thead><tr><th>Job</th><th>Company</th><th>Type</th><th>Applied</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentApps as $a): ?>
            <tr>
                <td><a href="../job.php?id=<?= $a['job_id'] ?>"><?= sanitize($a['job_title']) ?></a></td>
                <td><?= sanitize($a['company']) ?></td>
                <td><span class="badge <?= jobTypeBadge($a['job_type']) ?>"><?= $a['job_type'] ?></span></td>
                <td><?= timeAgo($a['created_at']) ?></td>
                <td><span class="badge <?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Recommended Jobs -->
    <?php if (!empty($recommended)): ?>
    <div class="section-header"><h2>Recommended for You</h2><a href="../jobs.php" class="btn btn-sm btn-outline">See More</a></div>
    <div class="jobs-grid">
        <?php foreach ($recommended as $j): ?>
        <div class="job-card">
            <div class="job-card-header">
                <div>
                    <div class="job-company">🏢 <?= sanitize($j['company']) ?></div>
                    <div class="job-title"><a href="../job.php?id=<?= $j['id'] ?>"><?= sanitize($j['title']) ?></a></div>
                </div>
                <span class="badge <?= jobTypeBadge($j['job_type']) ?>"><?= $j['job_type'] ?></span>
            </div>
            <div class="job-meta"><span>📍 <?= sanitize($j['location']) ?></span><span>⏰ <?= timeAgo($j['created_at']) ?></span></div>
            <div class="job-card-footer">
                <span class="job-salary"><?= formatSalary($j['salary_min'], $j['salary_max']) ?></span>
                <a href="apply.php?job_id=<?= $j['id'] ?>" class="btn btn-sm btn-primary">Apply</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
