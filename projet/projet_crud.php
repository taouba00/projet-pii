<?php
// projet_crud.php - Handle CRUD operations for projects (MySQLi)
require_once '../login/db.php';
header('Content-Type: application/json');

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'read') {
    $result = $conn->query('SELECT * FROM projects ORDER BY created_at DESC');
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    echo json_encode(['success' => true, 'projects' => $projects]);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'] ?: null;
    $status = $_POST['status'];
    $stmt = $conn->prepare('UPDATE projects SET name=?, description=?, start_date=?, end_date=?, status=?, updated_at=NOW() WHERE id=?');
    $stmt->bind_param('sssssi', $name, $description, $start_date, $end_date, $status, $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare('DELETE FROM projects WHERE id=?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

if ($action === 'create') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'] ?: null;
    $status = $_POST['status'];
    $author_id = isset($_POST['author_id']) ? intval($_POST['author_id']) : null;
    $stmt = $conn->prepare('INSERT INTO projects (name, description, start_date, end_date, status, created_at, updated_at, author_id) VALUES (?, ?, ?, ?, ?, NOW(), NOW(), ?)');
    $stmt->bind_param('sssssi', $name, $description, $start_date, $end_date, $status, $author_id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok, 'id' => $conn->insert_id]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
