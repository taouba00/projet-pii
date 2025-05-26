<?php
// gestioncours_crud.php - Handle CRUD operations for courses (MySQLi)
session_start();
require_once '../../login/db.php';
if (!empty($_POST['action'])) {
    header('Content-Type: application/json');
}
ini_set('display_errors', 0);
$action = $_POST['action'] ?? '';

// Helper: handle file uploads and return JSON-encoded file paths
function handleCourseFiles($inputName, $existingFiles = []) {
    $uploadDir = '../../Home/cours_files/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $filesArr = $existingFiles;
    if (!empty($_FILES[$inputName]) && is_array($_FILES[$inputName]['name'])) {
        foreach ($_FILES[$inputName]['name'] as $i => $name) {
            if ($_FILES[$inputName]['error'][$i] === UPLOAD_ERR_OK) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid('cours_') . '.' . $ext;
                $target = $uploadDir . $filename;
                if (move_uploaded_file($_FILES[$inputName]['tmp_name'][$i], $target)) {
                    $filesArr[] = 'Home/cours_files/' . $filename;
                }
            }
        }
    }
    return json_encode($filesArr);
}

if ($action === 'read') {
    $result = $conn->query('SELECT * FROM courses ORDER BY created_at DESC');
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $row['files'] = $row['files'] ? json_decode($row['files'], true) : [];
        $courses[] = $row;
    }
    echo json_encode(['success' => true, 'courses' => $courses]);
    exit;
}

if ($action === 'create') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $files = handleCourseFiles('files');
    $stmt = $conn->prepare('INSERT INTO courses (titre, description, contenu, status, files, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'DB prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('sssss', $titre, $description, $contenu, $status, $files);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok, 'id' => $conn->insert_id]);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $existingFiles = isset($_POST['existingFiles']) ? json_decode($_POST['existingFiles'], true) : [];
    $files = handleCourseFiles('files', $existingFiles);
    $stmt = $conn->prepare('UPDATE courses SET titre=?, description=?, contenu=?, status=?, files=?, updated_at=NOW() WHERE id=?');
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'DB prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('sssssi', $titre, $description, $contenu, $status, $files, $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    // Remove files from disk
    $res = $conn->query('SELECT files FROM courses WHERE id=' . $id);
    if ($res && $row = $res->fetch_assoc()) {
        $files = $row['files'] ? json_decode($row['files'], true) : [];
        foreach ($files as $file) {
            $path = '../../' . $file;
            if (file_exists($path)) @unlink($path);
        }
    }
    $stmt = $conn->prepare('DELETE FROM courses WHERE id=?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
