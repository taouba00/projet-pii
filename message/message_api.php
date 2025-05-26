<?php
session_start();
require_once '../login/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_chats') {
    // Debug message for API call
    error_log('message_api.php: get_chats called by user_id=' . $user_id . ' at ' . date('Y-m-d H:i:s'));
    $sql = 'SELECT c.*, 
                u1.id as user1_id, u1.nom as user1_nom, u1.prenom as user1_prenom, u1.email as user1_email,
                u2.id as user2_id, u2.nom as user2_nom, u2.prenom as user2_prenom, u2.email as user2_email
            FROM chats c
            JOIN users u1 ON c.user1_id = u1.id
            JOIN users u2 ON c.user2_id = u2.id
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.created_at DESC';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $chats = [];
    while ($row = $result->fetch_assoc()) {
        $chat = [
            'id' => $row['id'],
            'user1_id' => $row['user1_id'],
            'user2_id' => $row['user2_id'],
            'created_at' => $row['created_at'],
            'user1' => [
                'id' => $row['user1_id'],
                'nom' => $row['user1_nom'],
                'prenom' => $row['user1_prenom'],
                'email' => $row['user1_email'],
            ],
            'user2' => [
                'id' => $row['user2_id'],
                'nom' => $row['user2_nom'],
                'prenom' => $row['user2_prenom'],
                'email' => $row['user2_email'],
            ]
        ];
        $chats[] = $chat;
    }

    echo json_encode([
        'success' => true,
        'message' => 'get_chats endpoint called',
        'chats' => $chats,
        'debug' => empty($chats) ? 'Aucun chat trouvé pour user_id=' . $user_id : null
    ]);
    exit;
}

if ($action === 'get_messages') {
    $chat_id = isset($_GET['chat_id']) ? intval($_GET['chat_id']) : 0;
    if (!$chat_id) {
        echo json_encode(['success' => false, 'error' => 'Missing chat_id']);
        exit;
    }

    $stmt = $conn->prepare('SELECT * FROM messages WHERE chat_id = ? ORDER BY created_at ASC');
    $stmt->bind_param('i', $chat_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

if ($action === 'send_message') {
    $chat_id = isset($_POST['chat_id']) ? intval($_POST['chat_id']) : 0;
    $contenu = trim($_POST['content'] ?? '');

    if (!$chat_id || !$contenu) {
        echo json_encode(['success' => false, 'error' => 'Missing chat_id or content']);
        exit;
    }

    $stmt = $conn->prepare('INSERT INTO messages (chat_id, sender_id, contenu, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iis', $chat_id, $user_id, $contenu);
    $ok = $stmt->execute();

    if (!$ok) {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    } else {
        echo json_encode(['success' => true]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
