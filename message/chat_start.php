<?php
// chat_start.php: Create or find a chat between the logged-in user and the proposition author, then redirect to message.php?chat_id=...
session_start();
require_once '../login/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /freelance/login/login.php');
    exit;
}

$logged_in_user = $_SESSION['user_id'];
$other_user = isset($_GET['author_id']) ? intval($_GET['author_id']) : 0;


// Find existing chat
$stmt = $conn->prepare('SELECT id FROM chats WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?) LIMIT 1');
$stmt->bind_param('iiii', $logged_in_user, $other_user, $other_user, $logged_in_user);
$stmt->execute();
$stmt->bind_result($chat_id);
if ($stmt->fetch()) {
    $stmt->close();
    header('Location: /freelance/message/message.php?chat_id=' . $chat_id);
    exit;
}
$stmt->close();

// Create new chat
$stmt = $conn->prepare('INSERT INTO chats (user1_id, user2_id, created_at) VALUES (?, ?, NOW())');
$stmt->bind_param('ii', $logged_in_user, $other_user);
if ($stmt->execute()) {
    $chat_id = $stmt->insert_id;
    $stmt->close();
    header('Location: /freelance/message/message.php?chat_id=' . $chat_id);
    exit;
} else {
    $stmt->close();
    // Pass error message as a GET parameter for display
    $error = urlencode('Erreur lors de la création du chat: ' . $conn->error);
    header('Location: /freelance/message/message.php?chat_error=' . $error);
    exit;
}
