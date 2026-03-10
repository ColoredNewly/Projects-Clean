<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch the role of the logged-in user
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$role = $stmt->fetchColumn();

// Fetch all users except the logged in user
$stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id != ? ORDER BY username ASC");
$stmt->execute([$user_id]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Messages - Select User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>

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

<div class="container mt-4">
    <h2>Select a user to message</h2>
    <hr>

    <?php if (empty($users)): ?>
        <div class="alert alert-info">No other users found.</div>
    <?php else: ?>
        <ul class="list-group">
            <?php foreach ($users as $user): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="chat.php?user_id=<?= $user['id'] ?>" class="text-decoration-none">
                        <?= htmlspecialchars($user['username']) ?>
                    </a>
                    <span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($user['role']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
