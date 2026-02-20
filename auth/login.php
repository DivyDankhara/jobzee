<?php
/**
 * JobZee - Login Page
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');

if (isLoggedIn()) {
    redirect('../index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch. Please try again.';
    } else {
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $errors[] = 'Email and password are required.';
        } else {
            $user = getUserByEmail($email);
            if ($user && password_verify($password, $user['password'])) {
                loginUser($user);
                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                $redirect = match ($user['role']) {
                    'admin'     => '../admin/dashboard.php',
                    'recruiter' => '../recruiter/dashboard.php',
                    default     => '../jobseeker/dashboard.php',
                };
                redirect($redirect);
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | JobZee</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="../index.php" class="logo">💼 JobZee</a>
            <div class="form-subtitle" style="margin-top:8px;">Welcome back! Sign in to continue.</div>
        </div>

        <?php foreach ($errors as $e): ?>
            <div class="alert alert-error">✗ <?= sanitize($e) ?></div>
        <?php endforeach; ?>
        <?= showFlash() ?>

        <form action="login.php" method="POST" data-validate>
            <?= csrfField() ?>
            <div class="form-group">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required data-label="Email">
            </div>
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Your password" required data-label="Password">
                    <button type="button" class="btn-eye" data-target="password" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1.1rem;">👁</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">Sign In →</button>
        </form>

        <div class="auth-switch">
            Don't have an account? <a href="register.php">Sign up free</a>
        </div>
        <div class="auth-switch" style="margin-top:12px; padding:12px; background:var(--gray-50); border-radius:var(--radius); font-size:.8rem;">
            <strong>Demo credentials:</strong><br>
            Admin: admin@jobzee.test<br>
            Recruiter: recruiter1@jobzee.test<br>
            Jobseeker: applicant1@jobzee.test<br>
            Password: <strong>password</strong>
        </div>
    </div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
