<?php
/**
 * JobZee - Edit Job
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('recruiter');

$db  = getDB();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { setFlash('error','Invalid job.'); redirect('manage_jobs.php'); }

// Fetch job - ensure ownership
$stmt = $db->prepare("SELECT * FROM jobs WHERE id = ? AND recruiter_id = ?");
$stmt->execute([$id, userId()]);
$job = $stmt->fetch();
if (!$job) { setFlash('error','Job not found or access denied.'); redirect('manage_jobs.php'); }

$errors = [];
$values = $job; // pre-fill from DB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch.';
    } else {
        $values = [
            'id'          => $id,
            'title'       => sanitize($_POST['title'] ?? ''),
            'company'     => sanitize($_POST['company'] ?? ''),
            'location'    => sanitize($_POST['location'] ?? ''),
            'salary_min'  => max(0, (int)($_POST['salary_min'] ?? 0)),
            'salary_max'  => max(0, (int)($_POST['salary_max'] ?? 0)),
            'job_type'    => in_array($_POST['job_type'] ?? '', ['full-time','part-time','contract','internship','remote']) ? $_POST['job_type'] : 'full-time',
            'description' => sanitize($_POST['description'] ?? ''),
            'deadline'    => sanitize($_POST['deadline'] ?? ''),
            'status'      => in_array($_POST['status'] ?? '', ['active','closed']) ? $_POST['status'] : 'active',
        ];

        if (!$values['title'])       $errors[] = 'Job title is required.';
        if (!$values['company'])     $errors[] = 'Company name is required.';
        if (!$values['location'])    $errors[] = 'Location is required.';
        if (!$values['description']) $errors[] = 'Description is required.';
        if (!$values['deadline'])    $errors[] = 'Deadline is required.';

        if (empty($errors)) {
            $upd = $db->prepare("UPDATE jobs SET title=?, company=?, location=?, salary_min=?, salary_max=?, job_type=?, description=?, deadline=?, status=? WHERE id=? AND recruiter_id=?");
            if ($upd->execute([$values['title'], $values['company'], $values['location'], $values['salary_min'], $values['salary_max'], $values['job_type'], $values['description'], $values['deadline'], $values['status'], $id, userId()])) {
                setFlash('success', 'Job updated successfully!');
                redirect('manage_jobs.php');
            } else {
                $errors[] = 'Update failed.';
            }
        }
    }
}

$pageTitle = 'Edit Job';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container-sm">
    <div style="margin-bottom:20px;"><a href="manage_jobs.php" style="color:var(--gray-500);font-size:.875rem;">← Back to Manage Jobs</a></div>
    <div class="form-card">
        <h1 class="form-title">Edit Job Listing</h1>
        <p class="form-subtitle">Update the details for this position.</p>

        <?php foreach ($errors as $e): ?><div class="alert alert-error">✗ <?= sanitize($e) ?></div><?php endforeach; ?>

        <form action="edit_job.php?id=<?= $id ?>" method="POST" data-validate>
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label">Job Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= sanitize($values['title']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Company <span class="required">*</span></label>
                    <input type="text" name="company" class="form-control" value="<?= sanitize($values['company']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location <span class="required">*</span></label>
                    <input type="text" name="location" class="form-control" value="<?= sanitize($values['location']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Min Salary ($)</label>
                    <input type="number" name="salary_min" class="form-control" value="<?= $values['salary_min'] ?: '' ?>" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Max Salary ($)</label>
                    <input type="number" name="salary_max" class="form-control" value="<?= $values['salary_max'] ?: '' ?>" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Job Type</label>
                    <select name="job_type" class="form-control">
                        <?php foreach (['full-time','part-time','contract','internship','remote'] as $t): ?>
                            <option value="<?= $t ?>" <?= $values['job_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" class="form-control" value="<?= $values['deadline'] ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-control">
                    <option value="active" <?= $values['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="closed" <?= $values['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Description <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="10" required><?= sanitize($values['description']) ?></textarea>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                <a href="manage_jobs.php" class="btn btn-outline btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
