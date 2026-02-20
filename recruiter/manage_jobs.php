<?php
/**
 * JobZee - Manage Jobs
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('recruiter');

$db  = getDB();
$uid = userId();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid token.');
    } else {
        $jid  = (int)($_POST['job_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM jobs WHERE id = ? AND recruiter_id = ?");
        $stmt->execute([$jid, $uid]);
        setFlash('success', 'Job deleted successfully.');
    }
    redirect('manage_jobs.php');
}

$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$stmt = $db->prepare("SELECT COUNT(*) FROM jobs WHERE recruiter_id = ?");
$stmt->execute([$uid]);
$total = $stmt->fetchColumn();
$pag   = paginate($total, $perPage, $page);

$stmt = $db->prepare("SELECT j.*, (SELECT COUNT(*) FROM applications WHERE job_id = j.id) AS app_count FROM jobs j WHERE j.recruiter_id = ? ORDER BY j.created_at DESC LIMIT {$perPage} OFFSET {$pag['offset']}");
$stmt->execute([$uid]);
$jobs = $stmt->fetchAll();

$pageTitle = 'Manage Jobs';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
    <div class="section-header mb-8">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">My Job Listings</h1>
            <p style="color:var(--gray-500);"><?= $total ?> total job<?= $total !== 1 ? 's' : '' ?></p>
        </div>
        <a href="create_job.php" class="btn btn-primary">+ Post New Job</a>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No jobs posted yet</h3>
            <p>Start attracting talent by posting your first job listing.</p>
            <a href="create_job.php" class="btn btn-primary">Post a Job</a>
        </div>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead>
                <tr><th>Job Title</th><th>Type</th><th>Location</th><th>Salary</th><th>Deadline</th><th>Apps</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php foreach ($jobs as $j): ?>
            <tr>
                <td>
                    <a href="../job.php?id=<?= $j['id'] ?>" style="font-weight:600;"><?= sanitize($j['title']) ?></a>
                    <br><small style="color:var(--gray-400)"><?= sanitize($j['company']) ?></small>
                </td>
                <td><span class="badge <?= jobTypeBadge($j['job_type']) ?>"><?= $j['job_type'] ?></span></td>
                <td><?= sanitize($j['location']) ?></td>
                <td><?= formatSalary($j['salary_min'], $j['salary_max']) ?></td>
                <td><?= date('M j, Y', strtotime($j['deadline'])) ?><?= isExpired($j['deadline']) ? '<br><small style="color:var(--danger)">Expired</small>' : '' ?></td>
                <td><a href="view_applications.php?job_id=<?= $j['id'] ?>" class="badge badge-blue"><?= $j['app_count'] ?></a></td>
                <td><span class="badge <?= $j['status'] === 'active' ? 'badge-green' : 'badge-gray' ?>"><?= $j['status'] ?></span></td>
                <td>
                    <div class="table-actions">
                        <a href="edit_job.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                        <a href="view_applications.php?job_id=<?= $j['id'] ?>" class="btn btn-sm btn-primary">Apps</a>
                        <form method="POST" action="manage_jobs.php" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                            <button type="submit" name="delete_job" class="btn btn-sm btn-danger" data-confirm="Delete '<?= sanitize($j['title']) ?>'? All applications will also be deleted.">Del</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= paginationHTML($pag, 'manage_jobs.php') ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
