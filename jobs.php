<?php
/**
 * JobZee - Browse Jobs Page
 */
require_once __DIR__ . '/includes/auth.php';

define('ROOT_PATH', '');
$pageTitle = 'Browse Jobs';

$db = getDB();

// Sanitize inputs
$search   = sanitize($_GET['q'] ?? '');
$location = sanitize($_GET['location'] ?? '');
$type     = sanitize($_GET['type'] ?? '');
$salMin   = (int)($_GET['salary_min'] ?? 0);
$salMax   = (int)($_GET['salary_max'] ?? 0);
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 9;

// Build query
$where  = ["j.status = 'active'", "j.deadline >= CURDATE()"];
$params = [];

if ($search) {
    $where[]  = "(j.title LIKE ? OR j.description LIKE ? OR j.company LIKE ?)";
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
}
if ($location) {
    $where[]  = "j.location LIKE ?";
    $params[] = "%$location%";
}
if ($type) {
    $where[]  = "j.job_type = ?";
    $params[] = $type;
}
if ($salMin > 0) {
    $where[]  = "j.salary_max >= ?";
    $params[] = $salMin;
}
if ($salMax > 0) {
    $where[]  = "j.salary_min <= ?";
    $params[] = $salMax;
}

$whereStr = implode(' AND ', $where);

// Count total
$countStmt = $db->prepare("SELECT COUNT(*) FROM jobs j WHERE $whereStr");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$pag = paginate($total, $perPage, $page);

// Fetch jobs
$stmt = $db->prepare("SELECT j.*, u.name AS recruiter_name FROM jobs j JOIN users u ON j.recruiter_id = u.id WHERE $whereStr ORDER BY j.created_at DESC LIMIT {$perPage} OFFSET {$pag['offset']}");
$stmt->execute($params);
$jobs = $stmt->fetchAll();

// Base URL for pagination
$queryParts = [];
foreach (['q' => $search, 'location' => $location, 'type' => $type, 'salary_min' => $salMin ?: '', 'salary_max' => $salMax ?: ''] as $k => $v) {
    if ($v !== '') $queryParts[] = "$k=" . urlencode($v);
}
$baseUrl = 'jobs.php' . ($queryParts ? '?' . implode('&', $queryParts) : '');

include __DIR__ . '/includes/header.php';
?>

<div class="page-container">
    <div class="section-header mb-8">
        <div>
            <h1 style="font-size:1.5rem; font-weight:800;">Browse Jobs</h1>
            <p style="color:var(--gray-500); font-size:.9rem;"><?= number_format($total) ?> job<?= $total !== 1 ? 's' : '' ?> found<?= $search ? ' for "' . sanitize($search) . '"' : '' ?></p>
        </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
        <form class="filters-form" action="jobs.php" method="GET" id="searchForm">
            <div class="form-group" style="flex:2; min-width:200px;">
                <label class="form-label">Keyword</label>
                <input type="text" name="q" id="searchKeyword" class="form-control" placeholder="Job title, skill, company..." value="<?= sanitize($search) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="City, State..." value="<?= sanitize($location) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Job Type</label>
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <?php foreach (['full-time','part-time','contract','internship','remote'] as $t): ?>
                        <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Min Salary ($)</label>
                <input type="number" name="salary_min" class="form-control" placeholder="e.g. 50000" value="<?= $salMin ?: '' ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Max Salary ($)</label>
                <input type="number" name="salary_max" class="form-control" placeholder="e.g. 150000" value="<?= $salMax ?: '' ?>">
            </div>
            <div class="form-group" style="display:flex; gap:8px; align-items:flex-end;">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="jobs.php" class="btn btn-outline">Clear</a>
            </div>
        </form>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>No jobs found</h3>
            <p>Try adjusting your search criteria or <a href="jobs.php">clear all filters</a>.</p>
        </div>
    <?php else: ?>
        <div class="jobs-grid">
            <?php foreach ($jobs as $job): ?>
            <div class="job-card">
                <?php if (isExpired($job['deadline'])): ?>
                    <span class="expired-badge">Expired</span>
                <?php endif; ?>
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
                    <span>📅 <?= date('M j, Y', strtotime($job['deadline'])) ?></span>
                </div>
                <div class="job-desc"><?= nl2br(sanitize(substr($job['description'], 0, 120))) ?>...</div>
                <div class="job-card-footer">
                    <span class="job-salary"><?= formatSalary($job['salary_min'], $job['salary_max']) ?></span>
                    <a href="job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-primary">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?= paginationHTML($pag, $baseUrl) ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
