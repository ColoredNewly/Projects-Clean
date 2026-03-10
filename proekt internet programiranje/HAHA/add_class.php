<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch username and role from database to avoid undefined variable warnings
$stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$username = $user['username'] ?? 'Unknown';
$role = $user['role'] ?? 'user';

$errors = [];
$success = '';

$event_types = [
    'Weightlifting',
    'Bodyweight Training',
    'Powerlifting',
    'Cardio',
    'HIIT Cardio',
    'Boxing',
    'BJJ',
    'Karate'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date_time = $_POST['date_time'] ?? '';
    $slots = (int)($_POST['slots'] ?? 0);
    $type = $_POST['type'] ?? '';

    if (!$name || !$date_time || $slots <= 0 || !in_array($type, $event_types)) {
        $errors[] = "Please fill all required fields correctly.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO classes (name, description, date_time, slots, trainer_id, type) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $description, $date_time, $slots, $user_id, $type])) {
            $success = "Class added successfully!";
        } else {
            $errors[] = "Failed to add class.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Class</title>
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

<div class="container" style="max-width: 600px;">
    <h2 class="mb-4">Add New Event</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error) echo "<li>" . htmlspecialchars($error) . "</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Event Name</label>
            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($name ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" rows="4" class="form-control"><?= htmlspecialchars($description ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="date_time" class="form-label">Date & Time</label>
            <input type="datetime-local" id="date_time" name="date_time" class="form-control" required value="<?= htmlspecialchars($date_time ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="slots" class="form-label">Slots</label>
            <input type="number" id="slots" name="slots" class="form-control" min="1" required value="<?= htmlspecialchars($slots ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="type" class="form-label">Type</label>
            <select id="type" name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <?php foreach ($event_types as $etype): ?>
                    <option value="<?= htmlspecialchars($etype) ?>" <?= (isset($type) && $type === $etype) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($etype) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Add Class</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
