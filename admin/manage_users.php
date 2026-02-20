<?php
/**
 * JobZee - Admin Manage Users
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('admin');

$db = getDB();

// Toggle user status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid token.');
    } else {
        $uid    = (int)($_POST['user_id'] ?? 0);
        $status = in_array($_POST['new_status'] ?? '', ['active','inactive']) ? $_POST['new_status'] : 'inactive';
        // Don't deactivate admins
        $stmt   = $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $stmt->execute([$status, $uid]);
        setFlash('success', 'User status updated.');
    }
    redirect('manage_users.php');
}

$search = sanitize($_GET['q'] ?? '');
$role   = sanitize($_GET['role'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = ["role != 'admin'"];
$params = [];
if ($search) { $where[] = "(name LIKE ? OR email LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($role)   { $where[] = "role = ?"; $params[] = $role; }
$whereStr = implode(' AND ', $where);

$cnt   = $db->prepare("SELECT COUNT(*) FROM users WHERE $whereStr"); $cnt->execute($params); $total = $cnt->fetchColumn();
$pag   = paginate($total, $perPage, $page);
$stmt  = $db->prepare("SELECT * FROM users WHERE $whereStr ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$pag['offset']}");
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container">
    <div class="section-header mb-8">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;">Manage Users</h1>
            <p style="color:var(--gray-500);"><?= $total ?> user<?= $total !== 1 ? 's' : '' ?></p>
        </div>
        <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
    </div>

    <div class="filters-bar" style="margin-bottom:24px;">
        <form class="filters-form" method="GET" action="manage_users.php">
            <div class="form-group" style="flex:2;">
                <label class="form-label">Search</label>
                <input type="text" name="q" class="form-control" placeholder="Name or email..." value="<?= sanitize($search) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="">All Roles</option>
                    <option value="recruiter" <?= $role === 'recruiter' ? 'selected' : '' ?>>Recruiter</option>
                    <option value="jobseeker" <?= $role === 'jobseeker' ? 'selected' : '' ?>>Job Seeker</option>
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end; display:flex; gap:8px;">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="manage_users.php" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td style="color:var(--gray-400)">#<?= $u['id'] ?></td>
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
                        <button type="submit" name="toggle_status" class="btn btn-sm <?= $u['status']==='active'?'btn-danger':'btn-success' ?>" data-confirm="<?= $u['status']==='active'?'Deactivate':'Activate' ?> <?= sanitize($u['name']) ?>?">
                            <?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?= paginationHTML($pag, "manage_users.php" . ($search ? "?q=$search" : '') . ($role ? "&role=$role" : '')) ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
