<?php
/**
 * JobZee - Admin Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('admin');

$db = getDB();

// Stats
$totalUsers    = $db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
$totalJobs     = $db->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
$activeJobs    = $db->query("SELECT COUNT(*) FROM jobs WHERE status='active' AND deadline >= CURDATE()")->fetchColumn();
$totalApps     = $db->query("SELECT COUNT(*) FROM applications")->fetchColumn();
$totalRec      = $db->query("SELECT COUNT(*) FROM users WHERE role='recruiter'")->fetchColumn();
$totalSeek     = $db->query("SELECT COUNT(*) FROM users WHERE role='jobseeker'")->fetchColumn();

// Recent users
$recentUsers = $db->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Recent jobs
$recentJobs = $db->query("SELECT j.*, u.name AS recruiter_name FROM jobs j JOIN users u ON j.recruiter_id = u.id ORDER BY j.created_at DESC LIMIT 8")->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-header" style="background:linear-gradient(135deg,#1e1b4b,#4f46e5);">
    <div class="container">
        <h1 class="dashboard-title">Admin Dashboard</h1>
        <p class="dashboard-subtitle">Platform overview and management</p>
    </div>
</div>

<div class="container dashboard-content">
    <div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
        <div class="stat-card"><div class="stat-icon blue">👥</div><div class="stat-info"><strong><?= $totalUsers ?></strong><span>Total Users</span></div></div>
        <div class="stat-card"><div class="stat-icon purple">🏢</div><div class="stat-info"><strong><?= $totalRec ?></strong><span>Recruiters</span></div></div>
        <div class="stat-card"><div class="stat-icon green">👤</div><div class="stat-info"><strong><?= $totalSeek ?></strong><span>Job Seekers</span></div></div>
        <div class="stat-card"><div class="stat-icon blue">📋</div><div class="stat-info"><strong><?= $totalJobs ?></strong><span>Total Jobs</span></div></div>
        <div class="stat-card"><div class="stat-icon green">✅</div><div class="stat-info"><strong><?= $activeJobs ?></strong><span>Active Jobs</span></div></div>
        <div class="stat-card"><div class="stat-icon orange">📨</div><div class="stat-info"><strong><?= $totalApps ?></strong><span>Applications</span></div></div>
    </div>

    <div style="display:flex; gap:12px; margin-bottom:32px; flex-wrap:wrap;">
        <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
        <a href="manage_jobs.php" class="btn btn-outline">Manage Jobs</a>
    </div>

    <!-- Recent Users -->
    <div class="section-header"><h2>Recent Users</h2><a href="manage_users.php" class="btn btn-sm btn-outline">View All</a></div>
    <div class="table-container mb-8">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
                <td><?= sanitize($u['name']) ?></td>
                <td><?= sanitize($u['email']) ?></td>
                <td><span class="badge <?= $u['role']==='recruiter'?'badge-blue':'badge-green' ?>"><?= $u['role'] ?></span></td>
                <td><span class="badge <?= $u['status']==='active'?'badge-green':'badge-red' ?>"><?= $u['status'] ?></span></td>
                <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <form method="POST" action="manage_users.php" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="new_status" value="<?= $u['status'] === 'active' ? 'inactive' : 'active' ?>">
                        <button type="submit" name="toggle_status" class="btn btn-sm <?= $u['status']==='active'?'btn-danger':'btn-success' ?>" data-confirm="<?= $u['status']==='active'?'Deactivate':'Activate' ?> this user?">
                            <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Recent Jobs -->
    <div class="section-header"><h2>Recent Jobs</h2><a href="manage_jobs.php" class="btn btn-sm btn-outline">View All</a></div>
    <div class="table-container">
        <table>
            <thead><tr><th>Title</th><th>Company</th><th>Recruiter</th><th>Type</th><th>Deadline</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($recentJobs as $j): ?>
            <tr>
                <td><a href="../job.php?id=<?= $j['id'] ?>"><?= sanitize($j['title']) ?></a></td>
                <td><?= sanitize($j['company']) ?></td>
                <td><?= sanitize($j['recruiter_name']) ?></td>
                <td><span class="badge <?= jobTypeBadge($j['job_type']) ?>"><?= $j['job_type'] ?></span></td>
                <td><?= date('M j, Y', strtotime($j['deadline'])) ?></td>
                <td><span class="badge <?= $j['status']==='active'?'badge-green':'badge-gray' ?>"><?= $j['status'] ?></span></td>
                <td>
                    <form method="POST" action="manage_jobs.php" style="display:inline;">
                        <?= csrfField() ?>
                        <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                        <button type="submit" name="delete_job" class="btn btn-sm btn-danger" data-confirm="Delete this job and all its applications?">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
