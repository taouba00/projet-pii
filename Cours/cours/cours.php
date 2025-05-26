<?php
require_once '../../login/db.php';
// Get all courses from DB
$courses = [];
$result = $conn->query('SELECT * FROM courses ORDER BY created_at DESC');
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Cours</title>
    <link rel="stylesheet" href="cours.css">

    <link rel="stylesheet" href="/composant/sidebar.css"> <!-- Si tu as du CSS pour la sidebar -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    

    <div class="main-content">
        <?php include_once '../../composant/sidebar.php'; ?>

        <div class="courses-container">
            <h1 class="page-title">Gestion des Cours</h1>
            <div class="search-container">
                <input type="text" id="searchInput" placeholder="Rechercher par titre ou description...">
                <div class="search-active-badge">!</div>
                <div class="search-results-counter"></div>
            </div>
            <div class="courses-list">
                <?php foreach ($courses as $course): ?>
                <div class="course-card-container">
                    <div class="course-card-link" style="cursor:pointer;" onclick="window.location.href='../gestioncours/show.php?id=<?= $course['id'] ?>'">
                        <div class="course-card">
                            <h2 class="course-title"><?= htmlspecialchars($course['titre']) ?></h2>
                            <p class="course-description"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                            <p class="course-date">Date de publication : <?= htmlspecialchars($course['created_at']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div id="pagination" class="pagination-controls"></div>
        </div>
    </div>

    <script src="cours.js"></script>
</body>
</html>
