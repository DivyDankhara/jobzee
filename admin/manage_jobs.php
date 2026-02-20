<?php
/**
 * JobZee - Admin Manage Jobs
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('admin');

$db = getDB();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_job'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid token.');
    } else {
        $jid  = (int)($_POST['job_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$jid]);
        setFlash('success', 'Job deleted.');
    }
    redirect('manage_jobs.php');
}

// Handle toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid token.');
    } else {
        $jid  = (int)($_POST['job_id'] ?? 0);
        $ns   = in_array($_POST['new_status'] ?? '', ['active','closed']) ? $_POST['new_status'] : 'active';
        $stmt = $db->prepare("UPDATE jobs SET status = ? WHERE id = ?");
        $stmt->execute([$ns, $jid]);
        setFlash('success', 'Job status updated.');
    }
    redirect('manage_jobs.php');
}

$search  = sanitize($_GET['q'] ?? '');
$type    = sanitize($_GET['type'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = ['1=1'];
$params = [];
if ($search) { $where[] = "(j.title LIKE ? OR j.company LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($type)   { $where[] = "j.job_type = ?"; $params[] = $type; }
$whereStr = implode(' AND ', $where);

$cnt   = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE $whereStr"); $cnt->execute($params); $total = $cnt->fetchColumn();
$pag   = paginate($total, $perPage, $page);
$stmt  = $db->prepare("SELECT j.*, u.name AS recruiter_name, (SELECT COUNT(*) FROM applications WHERE job_id = j.id) AS app_count FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE $whereStr ORDER BY j.created_at DESC LIMIT {$perPage} OFFSET {$pag['offset']}");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$pageTitle = 'Manage Jobs';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
    <div class="section-header mb-8">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">Manage Jobs</h1>
            <p style="color:var(--gray-500);"><?= $total ?> job<?= $total !== 1 ? 's' : '' ?> total</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
    </div>

    <div class="filters-bar" style="margin-bottom:24px;">
        <form class="filters-form" method="GET">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Title or company..." value="<?= sanitize($search) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <?php foreach (['full-time','part-time','contract','internship','remote'] as $t): ?>
                        <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end; display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="manage_jobs.php" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead><tr><th>Title</th><th>Company</th><th>Recruiter</th><th>Type</th><th>Deadline</th><th>Apps</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($jobs as $j): ?>
            <tr>
                <td><a href="../job.php?id=<?= $j['id'] ?>"><?= sanitize($j['title']) ?></a></td>
                <td><?= sanitize($j['company']) ?></td>
                <td><?= sanitize($j['recruiter_name']) ?></td>
                <td><span class="badge <?= jobTypeBadge($j['job_type']) ?>"><?= $j['job_type'] ?></span></td>
                <td><?= date('M j, Y', strtotime($j['deadline'])) ?><?= isExpired($j['deadline']) ? '<br><small style="color:var(--danger)">Exp.</small>' : '' ?></td>
                <td><?= $j['app_count'] ?></td>
                <td><span class="badge <?= $j['status']==='active'?'badge-green':'badge-gray' ?>"><?= $j['status'] ?></span></td>
                <td>
                    <div class="table-actions">
                        <form method="POST" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                            <input type="hidden" name="new_status" value="<?= $j['status']==='active'?'closed':'active' ?>">
                            <button type="submit" name="toggle_status" class="btn btn-sm btn-outline"><?= $j['status']==='active'?'Close':'Open' ?></button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <?= csrfField() ?>
                            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                            <button type="submit" name="delete_job" class="btn btn-sm btn-danger" data-confirm="Delete this job and ALL its applications?">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= paginationHTML($pag, "manage_jobs.php" . ($search ? "?q=$search" : '') . ($type ? "&type=$type" : '')) ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
