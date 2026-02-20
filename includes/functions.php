<?php
/**
 * JobZee - Helper Functions
 */

/**
 * Sanitize input string
 */
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden field
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRF() . '">';
}

/**
 * Redirect to URL
 */
function redirect(string $url): void {
    header("Location: $url");
    exit();
}

/**
 * Set flash message
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message HTML
 */
function showFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $icon = $flash['type'] === 'success' ? '✓' : ($flash['type'] === 'error' ? '✗' : 'ℹ');
    return '<div class="alert alert-' . $flash['type'] . '"><span>' . $icon . '</span> ' . sanitize($flash['message']) . '</div>';
}

/**
 * Format salary range
 */
function formatSalary(float $min, float $max): string {
    if ($min == 0 && $max == 0) return 'Negotiable';
    if ($min == 0) return '$' . number_format($max);
    if ($max == 0) return 'From $' . number_format($min);
    return '$' . number_format($min) . ' – $' . number_format($max);
}

/**
 * Time ago
 */
function timeAgo(string $datetime): string {
    $time = time() - strtotime($datetime);
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 604800) return floor($time/86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

/**
 * Get job type badge class
 */
function jobTypeBadge(string $type): string {
    $classes = [
        'full-time'  => 'badge-blue',
        'part-time'  => 'badge-purple',
        'contract'   => 'badge-orange',
        'internship' => 'badge-green',
        'remote'     => 'badge-teal',
    ];
    return $classes[$type] ?? 'badge-gray';
}

/**
 * Get application status badge class
 */
function statusBadge(string $status): string {
    $classes = [
        'received'   => 'badge-gray',
        'reviewing'  => 'badge-blue',
        'shortlisted'=> 'badge-purple',
        'rejected'   => 'badge-red',
        'hired'      => 'badge-green',
    ];
    return $classes[$status] ?? 'badge-gray';
}

/**
 * Check if job deadline has passed
 */
function isExpired(string $deadline): bool {
    return strtotime($deadline) < time();
}

/**
 * Paginate results
 */
function paginate(int $total, int $perPage, int $currentPage): array {
    $totalPages = (int)ceil($total / $perPage);
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => max(0, $offset),
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
    ];
}

/**
 * Render pagination HTML
 */
function paginationHTML(array $pag, string $baseUrl): string {
    if ($pag['total_pages'] <= 1) return '';
    $html = '<div class="pagination">';
    $sep = str_contains($baseUrl, '?') ? '&' : '?';
    if ($pag['has_prev']) {
        $html .= '<a href="' . $baseUrl . $sep . 'page=' . ($pag['current'] - 1) . '" class="page-link">&laquo; Prev</a>';
    }
    for ($i = max(1, $pag['current'] - 2); $i <= min($pag['total_pages'], $pag['current'] + 2); $i++) {
        $active = $i === $pag['current'] ? ' active' : '';
        $html .= '<a href="' . $baseUrl . $sep . 'page=' . $i . '" class="page-link' . $active . '">' . $i . '</a>';
    }
    if ($pag['has_next']) {
        $html .= '<a href="' . $baseUrl . $sep . 'page=' . ($pag['current'] + 1) . '" class="page-link">Next &raquo;</a>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * Handle resume file upload
 */
function uploadResume(array $file): array {
    $allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'];
    $allowedExts  = ['pdf', 'doc', 'docx'];
    $maxSize      = 5 * 1024 * 1024; // 5MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed. Please try again.'];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size must be under 5MB.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        return ['success' => false, 'error' => 'Only PDF, DOC, DOCX files are allowed.'];
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes)) {
        return ['success' => false, 'error' => 'Invalid file type.'];
    }

    $dir = __DIR__ . '/../assets/uploads/resumes/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = uniqid('resume_', true) . '.' . $ext;
    $path     = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => false, 'error' => 'Could not save file.'];
    }

    return ['success' => true, 'path' => 'assets/uploads/resumes/' . $filename];
}
