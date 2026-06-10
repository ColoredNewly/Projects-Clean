<?php
// =============================================================
// BizDirectory — Database Configuration
// Update these values with your hosting credentials
// =============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'bizdir');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a PDO connection to the database.
 * Throws PDOException on failure (caught by calling scripts).
 */
function getConnection(): PDO {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

// CORS — allow any origin (adjust for production)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
