<?php
session_start();
require_once '../login/db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User must be logged in']);
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'read') {
    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : null;
    $sql = 'SELECT * FROM propositions';
    $params = [];
    
    if ($project_id) {
        $sql .= ' WHERE project_id = ?';
        $params[] = $project_id;
    }
    
    $sql .= ' ORDER BY created_at DESC';
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param('i', ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $propositions = [];
    while ($row = $result->fetch_assoc()) {
        $propositions[] = $row;
    }
    echo json_encode(['success' => true, 'propositions' => $propositions]);
    exit;
}

if ($action === 'create') {
    $contenu = trim($_POST['contenu']);
    $budget = floatval($_POST['budget']);
    $date_creation = $_POST['date_creation'];
    $date_fin = $_POST['date_fin'];
    $project_id = intval($_POST['project_id']);
    $user_id = $_SESSION['user_id']; // Use logged in user's ID

    $stmt = $conn->prepare('INSERT INTO propositions (contenu, budget, date_creation, date_fin, project_id, author_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->bind_param('sdssii', $contenu, $budget, $date_creation, $date_fin, $project_id, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $contenu = trim($_POST['contenu']);
    $budget = floatval($_POST['budget']);
    $date_creation = $_POST['date_creation'];
    $date_fin = $_POST['date_fin'];

    $stmt = $conn->prepare('UPDATE propositions SET contenu = ?, budget = ?, date_creation = ?, date_fin = ? WHERE id = ?');
    $stmt->bind_param('sdssi', $contenu, $budget, $date_creation, $date_fin, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    
    $stmt = $conn->prepare('DELETE FROM propositions WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit;
}
