<?php
/**
 * JobZee - Header Include
 * Call startPage($title) before including, or just include directly.
 */
if (!isset($pageTitle)) $pageTitle = 'JobZee';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> | JobZee</title>
    <link rel="stylesheet" href="<?= ROOT_PATH ?>assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a href="<?= ROOT_PATH ?>index.php" class="logo">
            <span class="logo-icon">💼</span>JobZee
        </a>
        <nav class="main-nav">
            <a href="<?= ROOT_PATH ?>index.php">Home</a>
            <a href="<?= ROOT_PATH ?>jobs.php">Browse Jobs</a>
            <?php if (isLoggedIn()): ?>
                <?php if (userRole() === 'recruiter'): ?>
                    <a href="<?= ROOT_PATH ?>recruiter/dashboard.php">Dashboard</a>
                    <a href="<?= ROOT_PATH ?>recruiter/create_job.php" class="btn btn-sm btn-primary">Post a Job</a>
                <?php elseif (userRole() === 'jobseeker'): ?>
                    <a href="<?= ROOT_PATH ?>jobseeker/dashboard.php">Dashboard</a>
                <?php elseif (userRole() === 'admin'): ?>
                    <a href="<?= ROOT_PATH ?>admin/dashboard.php">Admin Panel</a>
                <?php endif; ?>
                <div class="nav-user">
                    <span>👤 <?= sanitize(userName()) ?></span>
                    <a href="<?= ROOT_PATH ?>auth/logout.php" class="btn btn-sm btn-outline">Logout</a>
                </div>
            <?php else: ?>
                <a href="<?= ROOT_PATH ?>auth/login.php" class="btn btn-sm btn-outline">Login</a>
                <a href="<?= ROOT_PATH ?>auth/register.php" class="btn btn-sm btn-primary">Sign Up</a>
            <?php endif; ?>
        </nav>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">☰</button>
    </div>
</header>
<main class="site-main">
<?= showFlash() ?>
