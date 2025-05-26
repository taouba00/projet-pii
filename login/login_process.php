<?php
// login_process.php - Handles login POST requests
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$errors = [];

if (empty($email)) {
    $errors[] = 'Email is required.';
}
if (empty($password)) {
    $errors[] = 'Password is required.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

$stmt = $conn->prepare('SELECT id, email, password, nom, prenom, role FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'errors' => ['Database error: ' . $conn->error]]);
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        // Set session variables as needed
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['role'] = $user['role'];
        if($user['role'] === 'admin') {
            // Admins should not be able to log in with a wrong password
            header('Location: /freelance/gestionuser');
            exit;
        }
        // Redirect to the homepage
        header('Content-Type: text/html');
        header('Location: /freelance/projet/projet.php');
        exit;
    } else {

        $_SESSION['login_error'] = 'Invalid password.';
        header('Location: login.php');
        exit;
    }
} else {
    $_SESSION['login_error'] = 'No user found with this email.';
    header('Location: login.php');
    exit;
}
