<?php
// create_process.php - Handles user registration POST requests
require_once 'db.php';



// DEBUG: Return all POST, FILES, SERVER, and ENV data for troubleshooting
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    header('Content-Type: application/json');
    echo json_encode([
        'POST' => $_POST,
        'FILES' => $_FILES,
        'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
        'HEADERS' => function_exists('getallheaders') ? getallheaders() : 'N/A',
        'SERVER' => $_SERVER,
        'ENV' => $_ENV,
        'RAW_INPUT' => file_get_contents('php://input'),
        'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? null,
        'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? null,
        'COOKIES' => $_COOKIE,
        'SESSION' => isset($_SESSION) ? $_SESSION : 'No session',
        'TIME' => date('c'),
    ], JSON_PRETTY_PRINT);
    exit;
}

header('Content-Type: application/json');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$password_confirmation = isset($_POST['password_confirmation']) ? $_POST['password_confirmation'] : '';
$nom = isset($_POST['nom']) ? trim($_POST['nom']) : '';
$prenom = isset($_POST['prenom']) ? trim($_POST['prenom']) : '';
$role = isset($_POST['role']) ? $_POST['role'] : 'client';
$errors = [];

// Validate required fields
if (empty($email)) $errors[] = 'Email is required.';
if (empty($password)) $errors[] = 'Password is required.';
if ($password !== $password_confirmation) $errors[] = 'Passwords do not match.';
if (empty($nom)) $errors[] = 'Last name is required.';
if (empty($prenom)) $errors[] = 'First name is required.';

// Check if email already exists
if (empty($errors)) {
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = 'Email already exists.';
    }
    $stmt->close();
}

// Handle file upload (profile photo)
$photo_path = null;
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . uniqid() . '.' . $ext;
    $upload_dir = '../Home/image/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $target = $upload_dir . $filename;
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
        $photo_path = 'Home/image/' . $filename;
    } else {
        $errors[] = 'Failed to upload profile photo.';
    }
}



if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'errors' => $errors,
        'debug' => [
            'email' => $email,
            'nom' => $nom,
            'prenom' => $prenom,
            'role' => $role,
            'photo_upload_error' => $_FILES['photo']['error'] ?? null,
            'POST' => $_POST,
            'FILES' => $_FILES,
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'HEADERS' => function_exists('getallheaders') ? getallheaders() : 'N/A',
            'SERVER' => $_SERVER,
            'ENV' => $_ENV,
            'RAW_INPUT' => file_get_contents('php://input'),
            'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? null,
            'CONTENT_LENGTH' => $_SERVER['CONTENT_LENGTH'] ?? null,
            'COOKIES' => $_COOKIE,
            'SESSION' => isset($_SESSION) ? $_SESSION : 'No session',
            'TIME' => date('c'),
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Check if user already exists
$stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $errors[] = 'Email already exists.';
    $stmt->close();
} else {
    $stmt->close();

    // Hash password
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Insert new user
    $stmt = $conn->prepare('INSERT INTO users (email, password, nom, prenom, role, photo) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssss', $email, $password_hash, $nom, $prenom, $role, $photo_path);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        $errors[] = 'Database error: ' . $stmt->error;
    }
    $stmt->close();
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
}

$conn->close();
