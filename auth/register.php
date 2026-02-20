<?php
/**
 * JobZee - Register Page
 */
require_once __DIR__ . '/../includes/auth.php';

define('ROOT_PATH', '../');

if (isLoggedIn()) redirect('../index.php');

$errors      = [];
$defaultRole = in_array($_GET['role'] ?? '', ['jobseeker','recruiter']) ? $_GET['role'] : 'jobseeker';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token mismatch.';
    } else {
        $name     = sanitize($_POST['name'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';
        $role     = in_array($_POST['role'] ?? '', ['jobseeker','recruiter']) ? $_POST['role'] : 'jobseeker';

        if (!$name || !$email || !$password) $errors[] = 'All fields are required.';
        if ($name && strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
        if ($password && strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'This email is already registered.';
            } else {
                if (createUser($name, $email, $password, $role)) {
                    $user = getUserByEmail($email);
                    loginUser($user);
                    setFlash('success', 'Account created! Welcome to JobZee, ' . $name . '!');
                    redirect($role === 'recruiter' ? '../recruiter/dashboard.php' : '../jobseeker/dashboard.php');
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$pageTitle = 'Create Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | JobZee</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="../index.php" class="logo">💼 JobZee</a>
            <div class="form-subtitle" style="margin-top:8px;">Create your free account</div>
        </div>

        <?php foreach ($errors as $e): ?>
            <div class="alert alert-error">✗ <?= sanitize($e) ?></div>
        <?php endforeach; ?>

        <!-- Role Tabs -->
        <div class="role-tabs">
            <div class="role-tab <?= $defaultRole === 'jobseeker' ? 'active' : '' ?>" data-role="jobseeker">🎯 Job Seeker</div>
            <div class="role-tab <?= $defaultRole === 'recruiter' ? 'active' : '' ?>" data-role="recruiter">🏢 Recruiter</div>
        </div>

        <form action="register.php" method="POST" data-validate>
            <?= csrfField() ?>
            <input type="hidden" name="role" id="roleInput" value="<?= $defaultRole ?>">

            <div class="form-group">
                <label class="form-label">Full Name <span class="required">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="John Smith" value="<?= sanitize($_POST['name'] ?? '') ?>" required data-label="Full name">
            </div>
            <div class="form-group">
                <label class="form-label">Email Address <span class="required">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>" required data-label="Email">
            </div>
            <div class="form-group">
                <label class="form-label">Password <span class="required">*</span></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Min. 8 characters" required data-label="Password">
                    <button type="button" class="btn-eye" data-target="password" style="position:absolute;right:12px;top:12px;background:none;border:none;cursor:pointer;font-size:1.1rem;">👁</button>
                </div>
                <div class="password-strength"><div class="password-strength-bar"></div></div>
                <div style="font-size:.75rem;margin-top:4px; display:flex; justify-content:space-between;">
                    <span class="form-hint">Use uppercase, numbers, and symbols</span>
                    <span id="strengthText" style="color:var(--gray-500);font-weight:500;"></span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password <span class="required">*</span></label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required data-label="Confirm password">
            </div>
            <button type="submit" class="btn btn-primary btn-full btn-lg">Create Account →</button>
        </form>

        <div class="auth-switch">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>
<script src="../assets/js/script.js"></script>
</body>
</html>
