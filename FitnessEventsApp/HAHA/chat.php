<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$my_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$other_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($other_id <= 0) {
    die("Invalid user.");
}

// Fetch other user info
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$other_id]);
$other_user = $stmt->fetch();
if (!$other_user) {
    die("User not found.");
}

$errors = [];
$success = '';

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if ($message === '') {
        $errors[] = "Message cannot be empty.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        if ($stmt->execute([$my_id, $other_id, $message])) {
            $success = "Message sent.";
        } else {
            $errors[] = "Failed to send message.";
        }
    }
}

// Fetch conversation messages
$stmt = $pdo->prepare("
    SELECT m.*, u.username AS sender_name 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id
    WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
    ORDER BY sent_at ASC
");
$stmt->execute([$my_id, $other_id, $other_id, $my_id]);
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Chat with <?= htmlspecialchars($other_user['username']) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  .chat-box {
    border: 1px solid #ddd;
    padding: 15px;
    height: 400px;
    overflow-y: auto;
    background: #f8f9fa;
    border-radius: 8px;
  }
  .message {
    max-width: 70%;
    padding: 10px 15px;
    margin-bottom: 10px;
    border-radius: 20px;
    word-wrap: break-word;
    font-size: 0.9rem;
  }
  .sent {
    background-color: #d1e7dd;
    margin-left: auto;
    text-align: right;
  }
  .received {
    background-color: #e2e3e5;
    margin-right: auto;
    text-align: left;
  }
  .timestamp {
    font-size: 0.75rem;
    color: #6c757d;
  }
</style>
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
    <h2>Chat with <?= htmlspecialchars($other_user['username']) ?></h2>
    <hr>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error) echo "<p class='mb-1'>$error</p>"; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="chat-box mb-3" id="chat-box">
        <?php if (empty($messages)): ?>
            <p class="text-muted">No messages yet. Start the conversation!</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message <?= $msg['sender_id'] == $my_id ? 'sent' : 'received' ?>">
                    <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong><br>
                    <?= nl2br(htmlspecialchars($msg['message'])) ?><br>
                    <small class="timestamp"><?= htmlspecialchars($msg['sent_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form method="post" class="d-flex flex-column gap-2">
        <textarea name="message" rows="3" class="form-control" placeholder="Type your message here..." required></textarea>
        <button type="submit" class="btn btn-primary align-self-end">Send</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto scroll chat box to bottom
var chatBox = document.getElementById('chat-box');
chatBox.scrollTop = chatBox.scrollHeight;
</script>

</body>
</html>
