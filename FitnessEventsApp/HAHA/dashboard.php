<?php
session_start();
require 'db.php'; // Make sure this path is correct

// Validate session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$username = $_SESSION['username'] ?? '';
$errors = [];
$success = '';

// After session_start and $_SESSION checks

$type_filter = $_GET['type'] ?? '';

// Fetch unique event/class types for the filter dropdown
$stmt = $pdo->query("SELECT DISTINCT type FROM classes");
$event_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch classes with participant count
if ($type_filter) {
    $stmt = $pdo->prepare("
        SELECT 
            classes.*, 
            users.username AS trainer_name,
            (SELECT COUNT(*) FROM reservations WHERE reservations.class_id = classes.id) AS participant_count
        FROM classes 
        JOIN users ON classes.trainer_id = users.id 
        WHERE type = ? 
        ORDER BY date_time ASC
    ");
    $stmt->execute([$type_filter]);
} else {
    $stmt = $pdo->query("
        SELECT 
            classes.*, 
            users.username AS trainer_name,
            (SELECT COUNT(*) FROM reservations WHERE reservations.class_id = classes.id) AS participant_count
        FROM classes 
        JOIN users ON classes.trainer_id = users.id 
        ORDER BY date_time ASC
    ");
}
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle reservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reserve_class_id'])) {
        $class_id = $_POST['reserve_class_id'];

        // Check if user already reserved
        $stmt = $pdo->prepare("SELECT 1 FROM reservations WHERE user_id = ? AND class_id = ?");
        $stmt->execute([$user_id, $class_id]);
        if ($stmt->fetch()) {
            $errors[] = "You have already reserved this class.";
        } else {
            // Reserve and decrease slot
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO reservations (user_id, class_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $class_id]);

                $stmt = $pdo->prepare("UPDATE classes SET slots = slots - 1 WHERE id = ? AND slots > 0");
                $stmt->execute([$class_id]);

                $pdo->commit();
                $success = "Class reserved successfully!";
                header("Location: dashboard.php?type=" . urlencode($type_filter));
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = "Reservation failed. Please try again.";
            }
        }
    }

    // Handle deletion by admin
    if (isset($_POST['delete_class_id']) && $role === 'admin') {
        $class_id = $_POST['delete_class_id'];
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ? AND trainer_id = ?");
        $stmt->execute([$class_id, $user_id]);
        $success = "Class deleted successfully.";
        header("Location: dashboard.php?type=" . urlencode($type_filter));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navigation Bar -->
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

<div class="container">

    <!-- Display Success/Error Messages -->
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Filter Dropdown -->
    <form method="get" class="mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="typeFilter" class="col-form-label">Filter by Type:</label>
            </div>
            <div class="col-auto">
                <select class="form-select" name="type" id="typeFilter" onchange="this.form.submit()">
                    <option value="">-- All --</option>
                    <?php foreach ($event_types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= $type_filter === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <!-- Display Available Classes -->
    <h2 class="mb-3">Available Events</h2>

    <?php if (empty($classes)): ?>
        <div class="alert alert-info">No classes found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Date & Time</th>
                        <th>Slots</th>
                        <th>Trainer</th>
                        <th>Participants</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                        <tr>
                            <td><?= htmlspecialchars($class['name']) ?></td>
                            <td><?= htmlspecialchars($class['type']) ?></td>
                            <td><?= nl2br(htmlspecialchars($class['description'])) ?></td>
                            <td><?= htmlspecialchars($class['date_time']) ?></td>
                            <td><?= (int)$class['slots'] ?></td>
                            <td><?= htmlspecialchars($class['trainer_name']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span><?= (int)$class['participant_count'] ?></span>
                                    <a href="participants.php?class_id=<?= (int)$class['id'] ?>" class="btn btn-outline-info btn-sm">View</a>
                                </div>
                            </td>
                            <td>
                                <?php if ($role === 'client'): ?>
                                    <?php
                                    $stmt2 = $pdo->prepare("SELECT 1 FROM reservations WHERE user_id = ? AND class_id = ?");
                                    $stmt2->execute([$user_id, $class['id']]);
                                    $reserved = $stmt2->fetch();
                                    ?>
                                    <?php if ($reserved): ?>
                                        <span class="badge bg-success">Reserved</span>
                                    <?php elseif ($class['slots'] > 0): ?>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="reserve_class_id" value="<?= (int)$class['id'] ?>">
                                            <button type="submit" class="btn btn-primary btn-sm">Reserve</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Full</span>
                                    <?php endif; ?>
                                <?php elseif ($role === 'admin' && $class['trainer_id'] == $user_id): ?>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                        <input type="hidden" name="delete_class_id" value="<?= (int)$class['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
