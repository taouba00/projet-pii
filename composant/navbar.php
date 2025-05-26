<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Navbar Only</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --primary-hover: #2980b9;
        }

        .navbar {
            background-color: rgb(31, 41, 55);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-left span {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: white;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="navbar-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <span>Freela-connect</span>
    </div>
</div>

<script>
    document.getElementById('menuToggle')?.addEventListener('click', () => {
        alert('Toggle sidebar triggered (à connecter avec le sidebar).');
    });
</script>

</body>
</html>
