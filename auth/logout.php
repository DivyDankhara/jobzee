<?php
require_once __DIR__ . '/../includes/auth.php';
define('ROOT_PATH', '../');
logoutUser();
session_start();
setFlash('success', 'You have been logged out successfully.');
header('Location: ../index.php');
exit();
