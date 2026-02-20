<?php
/**
 * JobZee - Create Job
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('recruiter');

$errors = [];
$values = ['title'=>'','company'=>'','location'=>'','salary_min'=>'','salary_max'=>'','job_type'=>'full-time','description'=>'','deadline'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch.';
    } else {
        $values = [
            'title'       => sanitize($_POST['title'] ?? ''),
            'company'     => sanitize($_POST['company'] ?? ''),
            'location'    => sanitize($_POST['location'] ?? ''),
            'salary_min'  => max(0, (int)($_POST['salary_min'] ?? 0)),
            'salary_max'  => max(0, (int)($_POST['salary_max'] ?? 0)),
            'job_type'    => in_array($_POST['job_type'] ?? '', ['full-time','part-time','contract','internship','remote']) ? $_POST['job_type'] : 'full-time',
            'description' => sanitize($_POST['description'] ?? ''),
            'deadline'    => sanitize($_POST['deadline'] ?? ''),
        ];

        if (!$values['title'])       $errors[] = 'Job title is required.';
        if (!$values['company'])     $errors[] = 'Company name is required.';
        if (!$values['location'])    $errors[] = 'Location is required.';
        if (!$values['description']) $errors[] = 'Job description is required.';
        if (!$values['deadline'])    $errors[] = 'Application deadline is required.';
        if ($values['deadline'] && strtotime($values['deadline']) < time()) $errors[] = 'Deadline must be a future date.';

        if (empty($errors)) {
            $db   = getDB();
            $stmt = $db->prepare("INSERT INTO jobs (recruiter_id, title, company, location, salary_min, salary_max, job_type, description, deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([userId(), $values['title'], $values['company'], $values['location'], $values['salary_min'], $values['salary_max'], $values['job_type'], $values['description'], $values['deadline']])) {
                setFlash('success', 'Job posted successfully!');
                redirect('manage_jobs.php');
            } else {
                $errors[] = 'Failed to create job. Please try again.';
            }
        }
    }
}

$pageTitle = 'Post a New Job';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container-sm">
    <div style="margin-bottom:20px;">
        <a href="dashboard.php" style="color:var(--gray-500);font-size:.875rem;">← Back to Dashboard</a>
    </div>
    <div class="form-card">
        <h1 class="form-title">Post a New Job</h1>
        <p class="form-subtitle">Fill in the details to attract the right candidates.</p>

        <?php foreach ($errors as $e): ?><div class="alert alert-error">✗ <?= sanitize($e) ?></div><?php endforeach; ?>

        <form action="create_job.php" method="POST" data-validate>
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label">Job Title <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Senior PHP Developer" value="<?= $values['title'] ?>" required data-label="Job title">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Company Name <span class="required">*</span></label>
                    <input type="text" name="company" class="form-control" placeholder="Your company name" value="<?= $values['company'] ?>" required data-label="Company">
                </div>
                <div class="form-group">
                    <label class="form-label">Location <span class="required">*</span></label>
                    <input type="text" name="location" class="form-control" placeholder="City, State or Remote" value="<?= $values['location'] ?>" required data-label="Location">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Min Salary ($/year)</label>
                    <input type="number" name="salary_min" class="form-control" placeholder="e.g. 60000" value="<?= $values['salary_min'] ?: '' ?>" min="0">
                    <div class="form-hint">Leave 0 for negotiable</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Max Salary ($/year)</label>
                    <input type="number" name="salary_max" class="form-control" placeholder="e.g. 90000" value="<?= $values['salary_max'] ?: '' ?>" min="0">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Job Type <span class="required">*</span></label>
                    <select name="job_type" class="form-control">
                        <?php foreach (['full-time','part-time','contract','internship','remote'] as $t): ?>
                            <option value="<?= $t ?>" <?= $values['job_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Application Deadline <span class="required">*</span></label>
                    <input type="date" name="deadline" class="form-control" value="<?= $values['deadline'] ?>" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required data-label="Deadline">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Job Description <span class="required">*</span></label>
                <textarea name="description" class="form-control" rows="10" placeholder="Describe the role, responsibilities, requirements, benefits..." required data-label="Description"><?= $values['description'] ?></textarea>
                <div class="form-hint">Include responsibilities, requirements, nice-to-haves, and perks.</div>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary btn-lg">Publish Job</button>
                <a href="dashboard.php" class="btn btn-outline btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
