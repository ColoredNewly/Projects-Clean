<?php 
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$success = '';
$errors = [];

// Cancel reservation (client only)
if ($role === 'client' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_class_id'])) {
    $class_id = (int)$_POST['cancel_class_id'];

    // Delete reservation
    $stmt = $pdo->prepare("DELETE FROM reservations WHERE user_id = ? AND class_id = ?");
    if ($stmt->execute([$user_id, $class_id])) {
        // Restore slot
        $pdo->prepare("UPDATE classes SET slots = slots + 1 WHERE id = ?")->execute([$class_id]);
        $success = "Reservation cancelled.";
    } else {
        $errors[] = "Failed to cancel reservation.";
    }
}

// Delete class (admin only) - only if the admin owns the class
if ($role === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_class_id'])) {
    $delete_class_id = (int)$_POST['delete_class_id'];

    // Check if this class belongs to the admin (trainer_id = $user_id)
    $check = $pdo->prepare("SELECT id FROM classes WHERE id = ? AND trainer_id = ?");
    $check->execute([$delete_class_id, $user_id]);
    if ($check->rowCount() === 1) {
        // Delete related reservations first
        $pdo->prepare("DELETE FROM reservations WHERE class_id = ?")->execute([$delete_class_id]);

        // Delete the class
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        if ($stmt->execute([$delete_class_id])) {
            $success = "Class deleted successfully.";
        } else {
            $errors[] = "Failed to delete class.";
        }
    } else {
        $errors[] = "You do not have permission to delete this class.";
    }
}

// Load events with participant count
if ($role === 'client') {
    $stmt = $pdo->prepare("
        SELECT c.*, u.username AS trainer_name, COUNT(r2.user_id) AS participant_count
        FROM classes c
        JOIN reservations r ON c.id = r.class_id
        JOIN users u ON c.trainer_id = u.id
        LEFT JOIN reservations r2 ON c.id = r2.class_id
        WHERE r.user_id = ?
        GROUP BY c.id
        ORDER BY c.date_time ASC
    ");
    $stmt->execute([$user_id]);
    $my_events = $stmt->fetchAll();
} else { // admin
    $stmt = $pdo->prepare("
        SELECT c.*, u.username AS trainer_name, COUNT(r.user_id) AS participant_count
        FROM classes c
        JOIN users u ON c.trainer_id = u.id
        LEFT JOIN reservations r ON c.id = r.class_id
        WHERE c.trainer_id = ?
        GROUP BY c.id
        ORDER BY c.date_time ASC
    ");
    $stmt->execute([$user_id]);
    $my_events = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Events</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
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

    <h2 class="mb-4">My Events</h2>

    <!-- Messages -->
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p class='mb-1'>$error</p>"; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (empty($my_events)): ?>
        <p>No events found.</p>
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
            <?php foreach ($my_events as $event): ?>
                <tr>
                    <td><?= htmlspecialchars($event['name']) ?></td>
                    <td><?= htmlspecialchars($event['type']) ?></td>
                    <td><?= nl2br(htmlspecialchars($event['description'])) ?></td>
                    <td><?= htmlspecialchars($event['date_time']) ?></td>
                    <td><?= htmlspecialchars($event['slots']) ?></td>
                    <td><?= htmlspecialchars($event['trainer_name']) ?></td>
                    <td>
                        <?= htmlspecialchars($event['participant_count']) ?>
                        <!-- <a href="participants.php?class_id=<?= $event['id'] ?>" class="btn btn-sm btn-primary ms-2">View</a> -->
                        <!-- <a class="btn btn-outline-info btn-sm" href="participants.php?class_id=<?= $class['id'] ?>">View</a></td> -->
                        <a href="participants.php?class_id=<?= $event['id'] ?>" class="btn btn-outline-info btn-sm">View</a>
                    </td>
                    <td>
                        <?php if ($role === 'client'): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Cancel this reservation?');">
                                <input type="hidden" name="cancel_class_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-warning">Cancel</button>
                            </form>
                        <?php elseif ($role === 'admin'): ?>
                            <!-- <a href="participants.php?class_id=<?= $event['id'] ?>" class="btn btn-sm btn-info me-1">View Participants</a> -->
                            <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this class?');">
                                <input type="hidden" name="delete_class_id" value="<?= $event['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <?php endif; ?>

</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
