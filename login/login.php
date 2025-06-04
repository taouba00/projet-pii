<?php
session_start();
$error_message = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
$auth_error = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
$session_data = print_r($_SESSION, true);
unset($_SESSION['login_error']);
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Custom CSS -->
    <link href="login.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
            <h3 class="text-center mb-4">Login</h3>
              <!-- Error message container -->
            <?php if ($error_message || $auth_error): ?>
            <div class="alert alert-danger" id="error-container">
                <ul id="error-list">
                    <?php if ($error_message): ?>
                        <li><?php echo htmlspecialchars($error_message); ?></li>
                    <?php endif; ?>
                    <?php if ($auth_error): ?>
                        <li><?php echo htmlspecialchars($auth_error); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <?php endif; ?>

            <form id="loginForm" action="login_process.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" id="loginBtn" class="btn btn-primary">Login</button>
                </div>

                <div class="text-center mt-3">
                    <a href="create.php" class="btn-create">Create User</a>
                </div>
            </form>
        </div>
    </div>

    <!-- JS -->
    <script src="login.js"></script>
</body>
</html>
