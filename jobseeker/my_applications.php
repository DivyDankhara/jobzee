<?php
/**
 * JobZee - My Applications (Jobseeker)
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('jobseeker');

$db      = getDB();
$uid     = userId();
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$status  = sanitize($_GET['status'] ?? '');

$where  = ["a.applicant_id = ?"];
$params = [$uid];
if ($status) { $where[] = "a.status = ?"; $params[] = $status; }
$whereStr = implode(' AND ', $where);

$cnt  = $db->prepare("SELECT COUNT(*) FROM applications a WHERE $whereStr"); $cnt->execute($params); $total = $cnt->fetchColumn();
$pag  = paginate($total, $perPage, $page);

$stmt = $db->prepare("SELECT a.*, j.title AS job_title, j.company, j.location, j.job_type, j.salary_min, j.salary_max, j.deadline FROM applications a JOIN jobs j ON a.job_id = j.id WHERE $whereStr ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$pag['offset']}");
$stmt->execute($params);
$apps = $stmt->fetchAll();

$pageTitle = 'My Applications';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
    <div class="section-header mb-8">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">My Applications</h1>
            <p style="color:var(--gray-500);"><?= $total ?> application<?= $total !== 1 ? 's' : '' ?></p>
        </div>
        <a href="../jobs.php" class="btn btn-primary">Browse More Jobs</a>
    </div>

    <!-- Status filter -->
    <div style="display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap;">
        <?php foreach ([''=>'All', 'received'=>'Received', 'reviewing'=>'Reviewing', 'shortlisted'=>'Shortlisted', 'rejected'=>'Rejected', 'hired'=>'Hired'] as $val => $label): ?>
            <a href="my_applications.php<?= $val ? '?status='.$val : '' ?>" class="btn btn-sm <?= $status === $val ? 'btn-primary' : 'btn-outline' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($apps)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No applications<?= $status ? ' with status "' . sanitize($status) . '"' : '' ?></h3>
            <p>Find and apply to your dream job today!</p>
            <a href="../jobs.php" class="btn btn-primary">Browse Jobs</a>
        </div>
    <?php else: ?>
    <div class="table-container">
        <table>
            <thead><tr><th>Job</th><th>Company</th><th>Type</th><th>Salary</th><th>Deadline</th><th>Applied</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($apps as $a): ?>
            <tr>
                <td><a href="../job.php?id=<?= $a['job_id'] ?>" style="font-weight:600;"><?= sanitize($a['job_title']) ?></a></td>
                <td><?= sanitize($a['company']) ?><br><small style="color:var(--gray-400)">📍 <?= sanitize($a['location']) ?></small></td>
                <td><span class="badge <?= jobTypeBadge($a['job_type']) ?>"><?= $a['job_type'] ?></span></td>
                <td><?= formatSalary($a['salary_min'], $a['salary_max']) ?></td>
                <td><?= date('M j, Y', strtotime($a['deadline'])) ?><?= isExpired($a['deadline']) ? '<br><small style="color:var(--danger)">Expired</small>' : '' ?></td>
                <td><?= timeAgo($a['created_at']) ?></td>
                <td>
                    <span class="badge <?= statusBadge($a['status']) ?>"><?= ucfirst($a['status']) ?></span>
                    <?php if ($a['status'] === 'hired'): ?>
                        <div style="font-size:.75rem; color:var(--success); margin-top:4px;">🎉 Congratulations!</div>
                    <?php elseif ($a['status'] === 'shortlisted'): ?>
                        <div style="font-size:.75rem; color:var(--primary); margin-top:4px;">⭐ Great news!</div>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= paginationHTML($pag, "my_applications.php" . ($status ? "?status=$status" : '')) ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
