<?php
require_once '../../login/db.php';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$course = null;
if ($id) {
    $stmt = $conn->prepare('SELECT * FROM courses WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    $stmt->close();
}
if (!$course) {
    echo '<p style="color:red;text-align:center;">Cours introuvable.</p>';
    exit;
}
$files = $course['files'] ? json_decode($course['files'], true) : [];
function is_image($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','bmp','webp']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Cours</title>
    <link rel="stylesheet" href="showcours.css">
</head>
<body>
    <div class="course-details-container">
        <h1 class="course-title"><?= htmlspecialchars($course['titre']) ?></h1>
        <p class="course-description">Description : <?= nl2br(htmlspecialchars($course['description'])) ?></p>
        <p class="course-content">Contenu :<br><?= nl2br(htmlspecialchars($course['contenu'])) ?></p>
        <p class="course-status">Statut : <?= htmlspecialchars($course['status']) ?></p>
        <p class="course-date">Date de publication : <?= htmlspecialchars($course['created_at']) ?></p>
        <div class="course-files">
            <h3>Fichiers :</h3>
            <ul id="file-list">
                <?php if (empty($files)): ?>
                    <li>Aucun fichier.</li>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <li>
                            <?php if (is_image($file)): ?>
                                <img src="../../<?= htmlspecialchars($file) ?>" alt="Fichier du cours" class="file-image" style="max-width:150px;max-height:100px;vertical-align:middle;">
                                <a href="../../<?= htmlspecialchars($file) ?>" target="_blank">Voir l'image</a>
                            <?php else: ?>
                                <a href="../../<?= htmlspecialchars($file) ?>" download>Télécharger : <?= basename($file) ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        session_start();
        $is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
        $back_href = $is_admin ? 'gestioncours.php' : '../cours/cours.php';
        ?>
        <a href="<?= $back_href ?>" class="back-button" id="back-button">Retour aux cours</a>
    </div>
</body>
</html>
