<?php
session_start();
require_once __DIR__ . '/../login/db.php';
// Only set JSON header for AJAX requests
if (!empty($_POST['action'])) {
    header('Content-Type: application/json');
}
ini_set('display_errors', 0); // Hide PHP warnings/notices from AJAX JSON output

// Debugging log to check if 'action' parameter is received
if (!isset($_POST['action'])) {
    error_log('Action parameter is missing in the request.');
    echo json_encode(['success' => false, 'error' => 'Missing action parameter.']);
    exit;
}

$action = $_POST['action'];
error_log('Action parameter received: ' . $action);

if (!in_array($action, ['create', 'read','update', 'delete'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action: ' . htmlspecialchars($action)]);
    exit;
}

if ($action === 'read') {
    $result = $conn->query('SELECT * FROM posts ORDER BY created_at DESC');
    $posts = [];
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
    echo json_encode(['success' => true, 'posts' => $posts]);
    exit;
}

if ($action === 'create') {
    // Only allow if user is logged in
    if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour créer un post.']);
        exit;
    }
    $titre = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);
    $author_id = $_SESSION['user_id'];
    $image = null;
    $upload_error = null;
    error_log('CREATE POST: titre=' . $titre . ', contenu=' . $contenu . ', author_id=' . $author_id);
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid().'.'.$ext;
            $target = __DIR__ . '/../Home/image/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = 'Home/image/' . $filename;
            } else {
                $upload_error = 'Erreur lors de l\'upload de l\'image.';
            }
        } else {
            $upload_error = 'Erreur upload: ' . $_FILES['image']['error'];
        }
    } else {
        // Explicitly set $image to NULL if no file is uploaded
        $image = null;
    }
    // Ensure $image remains null if no file is uploaded or an error occurs
    $stmt = $conn->prepare('INSERT INTO posts (titre, contenu, image, author_id, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
    if (!$stmt) {
        error_log('DB PREPARE ERROR: ' . $conn->error);
        if (!empty($_POST['action'])) {
            echo json_encode(['success' => false, 'error' => 'DB prepare failed: ' . $conn->error]);
        } else {
            header('Location: post.php?error=1&dberror=1');
        }
        exit;
    }
    $stmt->bind_param('sssi', $titre, $contenu, $image, $author_id);
    $ok = $stmt->execute();
    if (!$ok) {
        error_log('DB EXECUTE ERROR: ' . $stmt->error);
    }
    if ($ok) {
        if (!empty($_POST['action'])) {
            // Redirect to post.php on success instead of returning JSON
            header('Location: post.php?success=1');
            exit;
        } else {
            header('Location: post.php?success=1');
        }
        exit;
    } else {
        if (!empty($_POST['action'])) {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        } else {
            header('Location: post.php?error=1&dberror=2');
        }
        exit;
    }
}

if ($action === 'update') {
    $id = intval($_POST['id']);
    $titre = trim($_POST['titre']);
    $contenu = trim($_POST['contenu']);
    
    // Get the current image from the database
    $stmt = $conn->prepare('SELECT image FROM posts WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $image = $row['image']; // Keep the existing image by default
    
    // Handle new image upload only if a file was actually uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid().'.'.$ext;
            $target = __DIR__ . '/../Home/image/' . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // Delete old image if it exists
                if ($currentImage && file_exists(__DIR__ . '/../' . $currentImage)) {
                    unlink(__DIR__ . '/../' . $currentImage);
                }
                $image = 'Home/image/' . $filename;
            } else {
                echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'upload de l\'image']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur upload: ' . $_FILES['image']['error']]);
            exit;
        }
    }

    $stmt = $conn->prepare('UPDATE posts SET titre=?, contenu=?, image=?, updated_at=NOW() WHERE id=?');
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'DB prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('sssi', $titre, $contenu, $image, $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok, 'image' => $image]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare('DELETE FROM posts WHERE id=?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    echo json_encode(['success' => $ok]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
