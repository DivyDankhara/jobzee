<?php
/**
 * JobZee - Recruiter Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('recruiter');

$db = getDB();
$uid = userId();

// Stats
$totalJobs    = $db->prepare("SELECT COUNT(*) FROM jobs WHERE recruiter_id = ?"); $totalJobs->execute([$uid]); $totalJobs = $totalJobs->fetchColumn();
$activeJobs   = $db->prepare("SELECT COUNT(*) FROM jobs WHERE recruiter_id = ? AND status='active' AND deadline >= CURDATE()"); $activeJobs->execute([$uid]); $activeJobs = $activeJobs->fetchColumn();
$totalApps    = $db->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.recruiter_id = ?"); $totalApps->execute([$uid]); $totalApps = $totalApps->fetchColumn();
$newApps      = $db->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.recruiter_id = ? AND a.status='received'"); $newApps->execute([$uid]); $newApps = $newApps->fetchColumn();

// Recent jobs
$stmt = $db->prepare("SELECT j.*, (SELECT COUNT(*) FROM applications WHERE job_id = j.id) AS app_count FROM jobs j WHERE j.recruiter_id = ? ORDER BY j.created_at DESC LIMIT 5");
$stmt->execute([$uid]);
$recentJobs = $stmt->fetchAll();

// Recent applications
$stmt2 = $db->prepare("SELECT a.*, j.title AS job_title, u.name AS applicant_name, u.email AS applicant_email FROM applications a JOIN jobs j ON a.job_id = j.id JOIN users u ON a.applicant_id = u.id WHERE j.recruiter_id = ? ORDER BY a.created_at DESC LIMIT 5");
$stmt2->execute([$uid]);
$recentApps = $stmt2->fetchAll();

$pageTitle = 'Recruiter Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-header">
    <div class="container">
        <h1 class="dashboard-title">Welcome back, <?= sanitize(userName()) ?>!</h1>
        <p class="dashboard-subtitle">Manage your job listings and applications</p>
    </div>
</div>

<div class="container dashboard-content">
    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">📋</div>
            <div class="stat-info"><strong><?= $totalJobs ?></strong><span>Total Jobs</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info"><strong><?= $activeJobs ?></strong><span>Active Jobs</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">📨</div>
            <div class="stat-info"><strong><?= $totalApps ?></strong><span>Total Applications</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">🆕</div>
            <div class="stat-info"><strong><?= $newApps ?></strong><span>New Applications</span></div>
        </div>
    </div>

    <!-- Quick actions -->
    <div style="display:flex; gap:12px; margin-bottom:32px; flex-wrap:wrap;">
        <a href="create_job.php" class="btn btn-primary">+ Post New Job</a>
        <a href="manage_jobs.php" class="btn btn-outline">Manage Jobs</a>
        <a href="view_applications.php" class="btn btn-outline">View Applications</a>
    </div>

    <!-- Recent Jobs -->
    <div class="section-header">
        <h2>Recent Job Listings</h2>
        <a href="manage_jobs.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-container mb-8">
        <?php if (empty($recentJobs)): ?>
            <div class="empty-state"><div class="empty-icon">📋</div><h3>No jobs posted yet</h3><p><a href="create_job.php">Post your first job</a> to get started.</p></div>
        <?php else: ?>
        <table>
            <thead><tr><th>Title</th><th>Type</th><th>Deadline</th><th>Applications</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($recentJobs as $j): ?>
            <tr>
                <td><a href="../job.php?id=<?= $j['id'] ?>"><?= sanitize($j['title']) ?></a><br><small style="color:var(--gray-400)"><?= sanitize($j['company']) ?></small></td>
                <td><span class="badge <?= jobTypeBadge($j['job_type']) ?>"><?= $j['job_type'] ?></span></td>
                <td><?= date('M j, Y', strtotime($j['deadline'])) ?><?= isExpired($j['deadline']) ? ' <span style="color:var(--danger);font-size:.75rem;">(expired)</span>' : '' ?></td>
                <td><span class="badge badge-blue"><?= $j['app_count'] ?> applied</span></td>
                <td><span class="badge <?= $j['status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= $j['status'] ?></span></td>
                <td>
                    <div class="table-actions">
                        <a href="edit_job.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="view_applications.php?job_id=<?= $j['id'] ?>" class="btn btn-sm btn-primary">Apps</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Recent Applications -->
    <div class="section-header">
        <h2>Recent Applications</h2>
        <a href="view_applications.php" class="btn btn-sm btn-outline">View All</a>
    </div>
    <div class="table-container">
        <?php if (empty($recentApps)): ?>
            <div class="empty-state"><div class="empty-icon">📨</div><h3>No applications yet</h3></div>
        <?php else: ?>
        <table>
            <thead><tr><th>Applicant</th><th>Job</th><th>Applied</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($recentApps as $a): ?>
            <tr>
                <td><?= sanitize($a['applicant_name']) ?><br><small style="color:var(--gray-400)"><?= sanitize($a['applicant_email']) ?></small></td>
                <td><a href="../job.php?id=<?= $a['job_id'] ?>"><?= sanitize($a['job_title']) ?></a></td>
                <td><?= timeAgo($a['created_at']) ?></td>
                <td><span class="badge <?= statusBadge($a['status']) ?>"><?= $a['status'] ?></span></td>
                <td><a href="view_applications.php?job_id=<?= $a['job_id'] ?>" class="btn btn-sm btn-outline">View</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
