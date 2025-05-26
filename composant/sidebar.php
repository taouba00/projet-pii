<?php
// Ensure this file hasn't been included yet
if (!defined('SIDEBAR_INCLUDED')) {
    define('SIDEBAR_INCLUDED', true);
    
    // Start output buffering if not already started
    if (ob_get_level() === 0) ob_start();
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_GET['logout'])) {
        // Clear output buffer first
        while (ob_get_level()) ob_end_clean();
        
        // Unset all session variables
        $_SESSION = [];

        // Destroy the session
        session_destroy();

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        // Perform redirect
        header('Location: /freelance/login/login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sidebar Only</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
            --sidebar-bg: #ffffff;
            --sidebar-text: #333333;
            --sidebar-active: #f1f8fe;
            --sidebar-hover: #e9f5fe;
            --sidebar-border: #e0e0e0;
            --sidebar-width: 230px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f0f0;
        }

        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--sidebar-bg);
            color: var(--sidebar-text);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-header h3 {
            margin: 0;
            color: var(--primary-color);
        }

        .toggle-sidebar {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background 0.2s;
            cursor: pointer;
        }

        .sidebar-menu li:hover {
            background-color: var(--sidebar-hover);
        }

        .sidebar-menu li i {
            margin-right: 12px;
        }

        .sidebar-menu li a {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .sidebar-footer {
            padding: 15px;
            text-align: center;
            font-size: 12px;
            border-top: 1px solid var(--sidebar-border);
            color: #777;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar.collapsed .sidebar-header h3,
        .sidebar.collapsed .sidebar-menu li span,
        .sidebar.collapsed .sidebar-footer {
            display: none;
        }

        .sidebar.collapsed .sidebar-menu li {
            justify-content: center;
        }

        .sidebar.collapsed .sidebar-menu li i {
            margin-right: 0;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div>
        <div class="sidebar-header">
            <h3>Menu</h3>
            <button class="toggle-sidebar" id="toggleSidebar"><i class="fas fa-chevron-left"></i></button>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="/freelance/projet/projet.php">
                    <i class="fas fa-project-diagram"></i><span>Projets</span>
                </a>
            </li>
            <li>
                <a href="/freelance/gestiondepost/post.php">
                    <i class="fas fa-file-alt"></i><span>Posts</span>
                </a>
            </li>
            <li>
                <a href="/freelance/cours/cours/cours.php">
                    <i class="fas fa-book"></i><span>Cours</span>
                </a>
            </li>
            <li>
                <a href="/freelance/message/message.php">
                    <i class="fas fa-envelope"></i><span>Messages</span>
                </a>
            </li>
            <li>
                <a href="/freelance/composant/sidebar.php?logout">
                    <i class="fas fa-sign-out-alt"></i><span>Déconnexion</span>
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-footer">
        &copy; 2025 Freela Connect
    </div>
</div>

<script>
    document.getElementById('toggleSidebar').addEventListener('click', function () {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');

        const icon = this.querySelector('i');
        icon.classList.toggle('fa-chevron-left');
        icon.classList.toggle('fa-chevron-right');
    });
</script>

</body>
</html>
