<?php
/**
 * JobZee - Apply for Job
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');
requireRole('jobseeker');

$db     = getDB();
$jobId  = (int)($_GET['job_id'] ?? 0);

if (!$jobId) { setFlash('error','Invalid job.'); redirect('../jobs.php'); }

$stmt = $db->prepare("SELECT * FROM jobs WHERE id = ? AND status='active' AND deadline >= CURDATE()");
$stmt->execute([$jobId]);
$job = $stmt->fetch();
if (!$job) { setFlash('error','This job is no longer accepting applications.'); redirect('../jobs.php'); }

// Already applied?
$check = $db->prepare("SELECT id FROM applications WHERE job_id = ? AND applicant_id = ?");
$check->execute([$jobId, userId()]);
if ($check->fetch()) { setFlash('error','You have already applied for this job.'); redirect("../job.php?id=$jobId"); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch.';
    } else {
        $coverLetter = sanitize($_POST['cover_letter'] ?? '');
        $resumePath  = null;

        // Handle file upload
        if (!empty($_FILES['resume']['name'])) {
            $upload = uploadResume($_FILES['resume']);
            if (!$upload['success']) {
                $errors[] = $upload['error'];
            } else {
                $resumePath = $upload['path'];
            }
        }

        if (empty($errors)) {
            $ins = $db->prepare("INSERT INTO applications (job_id, applicant_id, cover_letter, resume_path, status) VALUES (?, ?, ?, ?, 'received')");
            if ($ins->execute([$jobId, userId(), $coverLetter, $resumePath])) {
                setFlash('success', '🎉 Application submitted successfully! Good luck!');
                redirect('my_applications.php');
            } else {
                $errors[] = 'Submission failed. Please try again.';
            }
        }
    }
}

$pageTitle = 'Apply – ' . sanitize($job['title']);
include __DIR__ . '/../includes/header.php';
?>

<div class="page-container-sm">
    <div style="margin-bottom:20px;"><a href="../job.php?id=<?= $jobId ?>" style="color:var(--gray-500);font-size:.875rem;">← Back to Job</a></div>
    <div class="form-card">
        <h1 class="form-title">Apply for Job</h1>
        <div style="background:var(--primary-light); border-radius:var(--radius); padding:16px; margin-bottom:24px;">
            <div style="font-weight:700; color:var(--primary-dark);"><?= sanitize($job['title']) ?></div>
            <div style="font-size:.875rem; color:var(--gray-600); margin-top:4px;">🏢 <?= sanitize($job['company']) ?> · 📍 <?= sanitize($job['location']) ?> · <?= formatSalary($job['salary_min'], $job['salary_max']) ?></div>
        </div>

        <?php foreach ($errors as $e): ?><div class="alert alert-error">✗ <?= sanitize($e) ?></div><?php endforeach; ?>

        <form action="apply.php?job_id=<?= $jobId ?>" method="POST" enctype="multipart/form-data" data-validate>
            <?= csrfField() ?>

            <div class="form-group">
                <label class="form-label">Cover Letter</label>
                <textarea name="cover_letter" class="form-control" rows="8" placeholder="Tell the employer why you're a great fit for this role. Mention relevant experience, skills, and why you're interested."><?= sanitize($_POST['cover_letter'] ?? '') ?></textarea>
                <div class="form-hint">A strong cover letter significantly improves your chances.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Resume / CV</label>
                <div style="border:2px dashed var(--gray-300); border-radius:var(--radius); padding:24px; text-align:center; cursor:pointer;" onclick="document.getElementById('resume').click()">
                    <div style="font-size:2rem; margin-bottom:8px;">📎</div>
                    <div id="fileLabel" style="color:var(--gray-600); font-size:.875rem;">Click to upload your resume (PDF, DOC, DOCX · Max 5MB)</div>
                    <input type="file" name="resume" id="resume" accept=".pdf,.doc,.docx" style="display:none;">
                </div>
                <div class="form-hint">Accepted formats: PDF, DOC, DOCX. Maximum size: 5MB</div>
            </div>

            <div style="display:flex; gap:12px; margin-top:8px;">
                <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                <a href="../job.php?id=<?= $jobId ?>" class="btn btn-outline btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
