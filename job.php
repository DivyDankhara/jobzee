<?php
/**
 * JobZee - Job Detail Page
 */
require_once __DIR__ . '/includes/auth.php';

define('ROOT_PATH', '');

$db  = getDB();
$id  = (int)($_GET['id'] ?? 0);

if (!$id) {
    setFlash('error', 'Invalid job ID.');
    redirect('jobs.php');
}

$stmt = $db->prepare("SELECT j.*, u.name AS recruiter_name, u.email AS recruiter_email FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE j.id = ?");
$stmt->execute([$id]);
$job = $stmt->fetch();

if (!$job) {
    setFlash('error', 'Job not found.');
    redirect('jobs.php');
}

// Check if already applied
$alreadyApplied = false;
if (isLoggedIn() && userRole() === 'jobseeker') {
    $s2 = $db->prepare("SELECT id FROM applications WHERE job_id = ? AND applicant_id = ?");
    $s2->execute([$id, userId()]);
    $alreadyApplied = (bool)$s2->fetch();
}

$pageTitle = sanitize($job['title']) . ' at ' . sanitize($job['company']);
$expired   = isExpired($job['deadline']);

include __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <div style="margin-bottom:16px;">
        <a href="jobs.php" style="color:var(--gray-500); font-size:.875rem;">← Back to Jobs</a>
    </div>

    <div class="job-detail-header">
        <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:16px; align-items:flex-start;">
            <div>
                <h1 class="job-detail-title"><?= sanitize($job['title']) ?></h1>
                <div class="job-detail-company">🏢 <?= sanitize($job['company']) ?></div>
            </div>
            <span class="badge <?= jobTypeBadge($job['job_type']) ?>" style="font-size:.875rem; padding:6px 16px;"><?= $job['job_type'] ?></span>
        </div>

        <div class="job-detail-meta">
            <div class="meta-item">📍 <strong><?= sanitize($job['location']) ?></strong></div>
            <div class="meta-item">💰 <strong><?= formatSalary($job['salary_min'], $job['salary_max']) ?></strong></div>
            <div class="meta-item">📅 Deadline: <strong><?= date('F j, Y', strtotime($job['deadline'])) ?></strong></div>
            <div class="meta-item">🕐 Posted <?= timeAgo($job['created_at']) ?></div>
        </div>

        <?php if ($expired): ?>
            <div class="alert alert-warning">⚠️ This job listing has expired and is no longer accepting applications.</div>
        <?php elseif ($job['status'] === 'closed'): ?>
            <div class="alert alert-warning">This position has been closed by the recruiter.</div>
        <?php else: ?>
            <div class="job-detail-actions">
                <?php if (!isLoggedIn()): ?>
                    <a href="auth/login.php" class="btn btn-primary btn-lg">Login to Apply</a>
                    <a href="auth/register.php?role=jobseeker" class="btn btn-outline btn-lg">Create Account</a>
                <?php elseif (userRole() === 'jobseeker'): ?>
                    <?php if ($alreadyApplied): ?>
                        <button class="btn btn-success btn-lg" disabled>✓ Applied</button>
                    <?php else: ?>
                        <a href="jobseeker/apply.php?job_id=<?= $job['id'] ?>" class="btn btn-primary btn-lg">Apply Now</a>
                    <?php endif; ?>
                <?php elseif (userRole() === 'recruiter'): ?>
                    <div class="alert alert-info" style="margin:0;">Recruiters cannot apply for jobs.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="two-col">
        <div class="job-detail-body">
            <h3>Job Description</h3>
            <p><?= nl2br(sanitize($job['description'])) ?></p>
        </div>
        <div>
            <div class="card" style="margin-bottom:16px;">
                <h3 style="font-size:1rem; font-weight:700; margin-bottom:16px;">Job Overview</h3>
                <table style="font-size:.875rem;">
                    <tr><td style="color:var(--gray-500); padding-bottom:10px; padding-right:16px;">Type</td><td><span class="badge <?= jobTypeBadge($job['job_type']) ?>"><?= $job['job_type'] ?></span></td></tr>
                    <tr><td style="color:var(--gray-500); padding-bottom:10px;">Location</td><td><?= sanitize($job['location']) ?></td></tr>
                    <tr><td style="color:var(--gray-500); padding-bottom:10px;">Salary</td><td style="color:var(--success); font-weight:600;"><?= formatSalary($job['salary_min'], $job['salary_max']) ?></td></tr>
                    <tr><td style="color:var(--gray-500); padding-bottom:10px;">Deadline</td><td><?= date('M j, Y', strtotime($job['deadline'])) ?></td></tr>
                    <tr><td style="color:var(--gray-500);">Posted</td><td><?= date('M j, Y', strtotime($job['created_at'])) ?></td></tr>
                </table>
            </div>
            <?php if (!$expired && $job['status'] === 'active' && isLoggedIn() && userRole() === 'jobseeker' && !$alreadyApplied): ?>
            <a href="jobseeker/apply.php?job_id=<?= $job['id'] ?>" class="btn btn-primary btn-full btn-lg">Apply for this Job</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
