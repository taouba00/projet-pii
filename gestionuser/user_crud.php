<?php
session_start();
require_once '../login/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'read') {
    $result = $conn->query('SELECT id, email, nom, prenom, role FROM users ORDER BY id DESC');
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $role = trim($_POST['role'] ?? '');
    if (!$id || !$email || !$nom || !$prenom || !$role) {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        exit;
    }
    $stmt = $conn->prepare('UPDATE users SET email=?, nom=?, prenom=?, role=? WHERE id=?');
    $stmt->bind_param('ssssi', $email, $nom, $prenom, $role, $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Missing id']);
        exit;
    }
    $stmt = $conn->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
