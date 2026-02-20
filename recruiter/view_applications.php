<?php
/**
 * JobZee - View Applications (Recruiter)
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('recruiter');

$db  = getDB();
$uid = userId();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid token.');
    } else {
        $aid    = (int)($_POST['app_id'] ?? 0);
        $status = in_array($_POST['status'] ?? '', ['received','reviewing','shortlisted','rejected','hired']) ? $_POST['status'] : 'received';
        // Verify this app belongs to a job owned by recruiter
        $check  = $db->prepare("SELECT a.id FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ? AND j.recruiter_id = ?");
        $check->execute([$aid, $uid]);
        if ($check->fetch()) {
            $upd = $db->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $upd->execute([$status, $aid]);
            setFlash('success', 'Application status updated.');
        }
    }
    $qs = $_GET['job_id'] ? '?job_id=' . (int)$_GET['job_id'] : '';
    redirect("view_applications.php$qs");
}

$jobFilter = (int)($_GET['job_id'] ?? 0);
$statusFilter = sanitize($_GET['status'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 10;

// Build query
$where  = ["j.recruiter_id = ?"];
$params = [$uid];
if ($jobFilter) { $where[] = "a.job_id = ?"; $params[] = $jobFilter; }
if ($statusFilter) { $where[] = "a.status = ?"; $params[] = $statusFilter; }

$whereStr = implode(' AND ', $where);

$cnt  = $db->prepare("SELECT COUNT(*) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE $whereStr");
$cnt->execute($params);
$total = $cnt->fetchColumn();
$pag   = paginate($total, $perPage, $page);

$stmt = $db->prepare("SELECT a.*, j.title AS job_title, u.name AS applicant_name, u.email AS applicant_email FROM applications a JOIN jobs j ON a.job_id = j.id JOIN users u ON a.applicant_id = u.id WHERE $whereStr ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$pag['offset']}");
$stmt->execute($params);
$apps = $stmt->fetchAll();

// Jobs for filter dropdown
$jStmt = $db->prepare("SELECT id, title, company FROM jobs WHERE recruiter_id = ? ORDER BY created_at DESC");
$jStmt->execute([$uid]);
$myJobs = $jStmt->fetchAll();

$pageTitle = 'Applications';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
    <div class="section-header mb-8">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">Applications</h1>
            <p style="color:var(--gray-500);"><?= $total ?> application<?= $total !== 1 ? 's' : '' ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
    </div>

    <!-- Filters -->
    <div class="filters-bar" style="margin-bottom:24px;">
        <form class="filters-form" method="GET" action="view_applications.php">
            <div class="form-group">
                <label class="form-label">Filter by Job</label>
                <select name="job_id" class="form-control">
                    <option value="">All Jobs</option>
                    <?php foreach ($myJobs as $j): ?>
                        <option value="<?= $j['id'] ?>" <?= $jobFilter === (int)$j['id'] ? 'selected' : '' ?>><?= sanitize($j['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Filter by Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <?php foreach (['received','reviewing','shortlisted','rejected','hired'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end; display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="view_applications.php" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <?php if (empty($apps)): ?>
        <div class="empty-state">
            <div class="empty-icon">📨</div>
            <h3>No applications found</h3>
            <p>Applications will appear here when candidates apply to your jobs.</p>
        </div>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Applicant</th><th>Job</th><th>Applied</th><th>Cover Letter</th><th>Resume</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($apps as $a): ?>
            <tr>
                <td>
                    <strong><?= sanitize($a['applicant_name']) ?></strong>
                    <br><small style="color:var(--gray-400)"><?= sanitize($a['applicant_email']) ?></small>
                </td>
                <td><a href="../job.php?id=<?= $a['job_id'] ?>"><?= sanitize($a['job_title']) ?></a></td>
                <td><?= timeAgo($a['created_at']) ?></td>
                <td>
                    <?php if ($a['cover_letter']): ?>
                        <button onclick="document.getElementById('cl-<?= $a['id'] ?>').classList.toggle('hidden')" class="btn btn-sm btn-outline">Read</button>
                        <div id="cl-<?= $a['id'] ?>" class="hidden" style="margin-top:8px; padding:12px; background:var(--gray-50); border-radius:var(--radius); font-size:.8rem; max-width:300px; white-space:pre-wrap;"><?= sanitize($a['cover_letter']) ?></div>
                    <?php else: ?>
                        <span style="color:var(--gray-400);font-size:.8rem;">None</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($a['resume_path']): ?>
                        <a href="../<?= sanitize($a['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline">📎 Download</a>
                    <?php else: ?>
                        <span style="color:var(--gray-400);font-size:.8rem;">None</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST" action="view_applications.php" style="display:inline-flex; gap:8px; align-items:center;">
                        <?= csrfField() ?>
                        <input type="hidden" name="app_id" value="<?= $a['id'] ?>">
                        <select name="status" class="status-select" data-autosubmit>
                            <?php foreach (['received','reviewing','shortlisted','rejected','hired'] as $s): ?>
                                <option value="<?= $s ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= paginationHTML($pag, "view_applications.php" . ($jobFilter ? "?job_id=$jobFilter" : '') . ($statusFilter ? "&status=$statusFilter" : '')) ?>
    <?php endif; ?>
</div>

<style>.hidden { display: none !important; }</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
