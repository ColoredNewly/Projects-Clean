<?php
/**
 * GET /get_companies.php
 * Returns all companies as a JSON array.
 * Optional query param: ?category=Services
 */

require_once 'config.php';

try {
    $pdo = getConnection();

    $category = isset($_GET['category']) ? trim($_GET['category']) : '';

    if ($category !== '') {
        // Filter by category using FIND_IN_SET or LIKE
        $stmt = $pdo->prepare(
            "SELECT id, name, address, latitude, longitude,
                    email, phone, website, categories
             FROM companies
             WHERE FIND_IN_SET(:cat, REPLACE(categories, ', ', ',')) > 0
             ORDER BY name ASC"
        );
        $stmt->execute([':cat' => $category]);
    } else {
        $stmt = $pdo->query(
            "SELECT id, name, address, latitude, longitude,
                    email, phone, website, categories
             FROM companies
             ORDER BY name ASC"
        );
    }

    $companies = $stmt->fetchAll();

    // Cast numeric fields
    foreach ($companies as &$c) {
        $c['id']        = (int)   $c['id'];
        $c['latitude']  = (float) $c['latitude'];
        $c['longitude'] = (float) $c['longitude'];
    }

    echo json_encode($companies, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
