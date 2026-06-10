<?php
/**
 * POST /add_company.php
 * Inserts a new company record.
 *
 * Expected POST fields:
 *   name, address, latitude, longitude,
 *   email, phone, website, categories
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// ---- Read & sanitize input ----
$name       = trim($_POST['name']       ?? '');
$address    = trim($_POST['address']    ?? '');
$latitude   = (float) ($_POST['latitude']  ?? 0.0);
$longitude  = (float) ($_POST['longitude'] ?? 0.0);
$email      = trim($_POST['email']      ?? '');
$phone      = trim($_POST['phone']      ?? '');
$website    = trim($_POST['website']    ?? '');
$categories = trim($_POST['categories'] ?? '');

// ---- Validation ----
if ($name === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Name is required']);
    exit;
}
if ($address === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Address is required']);
    exit;
}
if ($categories === '') {
    http_response_code(400);
    echo json_encode(['error' => 'At least one category is required']);
    exit;
}

// Validate allowed categories
$allowed = ['Services', 'Entertainment', 'Industry', 'Education'];
$catList  = array_map('trim', explode(',', $categories));
foreach ($catList as $cat) {
    if (!in_array($cat, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => "Invalid category: $cat"]);
        exit;
    }
}

// ---- Insert ----
try {
    $pdo  = getConnection();
    $stmt = $pdo->prepare(
        "INSERT INTO companies
            (name, address, latitude, longitude, email, phone, website, categories)
         VALUES
            (:name, :address, :lat, :lng, :email, :phone, :website, :categories)"
    );

    $stmt->execute([
        ':name'       => $name,
        ':address'    => $address,
        ':lat'        => $latitude,
        ':lng'        => $longitude,
        ':email'      => $email,
        ':phone'      => $phone,
        ':website'    => $website,
        ':categories' => implode(',', $catList),
    ]);

    $newId = $pdo->lastInsertId();
    echo json_encode([
        'success' => true,
        'id'      => (int) $newId,
        'message' => 'Company saved successfully'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
