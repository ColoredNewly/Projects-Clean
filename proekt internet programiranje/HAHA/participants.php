<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch username and role for navbar
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$username = $user['username'] ?? 'Unknown';
$role = $user['role'] ?? 'user';

$class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
if ($class_id <= 0) {
    die('Invalid class ID.');
}

// Get class info
$stmt = $pdo->prepare("SELECT c.*, u.username AS trainer_name FROM classes c JOIN users u ON c.trainer_id = u.id WHERE c.id = ?");
$stmt->execute([$class_id]);
$class = $stmt->fetch();

if (!$class) {
    die('Class not found.');
}

// Get participants (users)
$stmt = $pdo->prepare("
    SELECT u.id, u.username 
    FROM reservations r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.class_id = ?
");
$stmt->execute([$class_id]);
$participants = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Participants for <?= htmlspecialchars($class['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

<!-- Navbar identical to dashboard -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Fitness Center</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="dashboard.php">Events</a></li>
                <li class="nav-item"><a class="nav-link" href="my_events.php">My Events</a></li>
                <?php if ($role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="add_class.php">Add Event</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>  
            </ul>
            <span class="navbar-text me-3 text-light">
                Welcome, <strong><?= htmlspecialchars($username) ?> (<?= htmlspecialchars($role) ?>)</strong>
            </span>
            <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container" style="max-width: 700px;">
    <h1 class="mb-3"><?= htmlspecialchars($class['name']) ?></h1>
    <div class="mb-3">
        <span class="badge bg-secondary"><?= htmlspecialchars($class['type']) ?></span>
    </div>
    <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($class['description'])) ?></p>
    <p><strong>Date & Time:</strong> <?= htmlspecialchars(date('F j, Y, g:i A', strtotime($class['date_time']))) ?></p>
    <p><strong>Trainer:</strong> <?= htmlspecialchars($class['trainer_name']) ?></p>

    <hr>

    <h3>Participants (<?= count($participants) ?>)</h3>

    <?php if (count($participants) === 0): ?>
        <p>No participants have reserved this class yet.</p>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($participants as $participant): ?>
                <li class="list-group-item"><?= htmlspecialchars($participant['username']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
