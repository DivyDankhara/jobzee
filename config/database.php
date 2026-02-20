<?php
/**
 * JobZee - Database Configuration
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jobzee');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get PDO database connection (singleton)
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="font-family:sans-serif;padding:40px;background:#fff0f0;border:1px solid #f00;margin:20px;border-radius:8px;">
                <h2>Database Connection Error</h2>
                <p>Could not connect to the database. Please check your configuration in <code>config/database.php</code>.</p>
                <p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Make sure MySQL is running and the <code>jobzee</code> database exists.</p>
            </div>');
        }
    }
    return $pdo;
}
