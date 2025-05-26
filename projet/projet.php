<?php
require_once '../login/db.php';

// Fetch all projects for display
$projects = [];
$result = $conn->query('SELECT * FROM projects ORDER BY created_at DESC');
while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Projet</title>
    <link rel="stylesheet" href="projet.css">
    <link rel="stylesheet" href="../composant/sidebar.css">
</head>
<body>
    <?php include_once '../composant/sidebar.php'; ?>
    <div class="posts-container">
        <h1 class="page-title">Projet</h1>

        <div class="post-form">
            <h2>Créer un nouveau Projet</h2>
            <form id="createProjectForm">
                <input type="text" name="name" placeholder="Nom du projet" required><br>
                <textarea name="description" placeholder="Description du projet" required></textarea><br>
                <input type="date" name="start_date" required><br>
                <input type="date" name="end_date"><br>
                <select name="status" required>
                    <option value="planned">Planned</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select><br>
                <button type="submit">Créer le Projet</button>
            </form>
        </div>

        <!-- Liste des projets -->
        <div class="posts-list" id="projects-container">
            <?php foreach ($projects as $project): ?>            <div class="post-card">
                <div class="post-card-link" onclick="window.location.href='../proposition/prorposition.php?project_id=<?= $project['id'] ?>'">
                    <div class="post-details">
                        <h2 class="post-title"><?= htmlspecialchars($project['name']) ?></h2>
                        <p class="post-content"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                        <p class="post-date">Début: <?= htmlspecialchars($project['start_date']) ?></p>
                        <p class="post-date">Fin: <?= htmlspecialchars($project['end_date']) ?></p>
                        <p class="post-status">Statut: <?= htmlspecialchars($project['status']) ?></p>
                    </div>
                </div>
                <div class="button-group">
                    <button onclick='openEditPopup({
                        id: <?= $project['id'] ?>,
                        name: "<?= addslashes($project['name']) ?>",
                        description: "<?= addslashes($project['description']) ?>",
                        start_date: "<?= $project['start_date'] ?>",
                        end_date: "<?= $project['end_date'] ?>",
                        status: "<?= $project['status'] ?>"
                    })' class="edit-button">Modifier</button>
                    <button onclick='deleteProject(<?= $project['id'] ?>)' class="delete-button">Supprimer</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Conteneur de pagination -->
        <div id="pagination-container" class="pagination"></div>
    </div>

    <!-- Popup édition -->
    <div id="editPopup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closeEditPopup()">&times;</span>
            <h2>Modifier le Projet</h2>
            <form id="editForm">
                <input type="text" id="editName" placeholder="Nom du projet" required><br>
                <textarea id="editDescription" placeholder="Description du projet" required></textarea><br>
                <label for="editStartDate">Date de début</label><br>
                <input type="date" id="editStartDate" required><br>
                <label for="editEndDate">Date de fin (optionnelle)</label><br>
                <input type="date" id="editEndDate"><br>
                <label for="editStatus">Statut</label><br>
                <select id="editStatus" required>
                    <option value="planned">Planned</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select><br>
                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>
    </div>

    <script src="projet.js"></script>
    <script>
    // Create project form handler
    document.getElementById('createProjectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'create');

        fetch('projet_crud.php', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Projet cree avec succes!');
                location.reload();
            } else {
                alert('Erreur lors de la creation du projet');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la creation du projet');
        });
    });

    // CRUD AJAX handlers
    function deleteProject(id) {
        if (!confirm('Supprimer ce projet ?')) return;
        fetch('projet_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erreur suppression'); });
    }

    // Edit form submit handler
    document.getElementById('editForm').onsubmit = function(e) {
        e.preventDefault();
        const id = window.currentEditId;
        const name = document.getElementById('editName').value;
        const description = document.getElementById('editDescription').value;
        const start_date = document.getElementById('editStartDate').value;
        const end_date = document.getElementById('editEndDate').value;
        const status = document.getElementById('editStatus').value;
        fetch('projet_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&id=${id}&name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}&start_date=${start_date}&end_date=${end_date}&status=${status}`
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erreur modification'); });
    };

    // Patch openEditPopup to set currentEditId
    window.openEditPopup = function(project) {
        window.currentEditId = project.id;
        document.getElementById('editName').value = project.name;
        document.getElementById('editDescription').value = project.description;
        document.getElementById('editStartDate').value = project.start_date;
        document.getElementById('editEndDate').value = project.end_date || '';
        document.getElementById('editStatus').value = project.status;
        document.getElementById('editPopup').style.display = 'flex';
        setTimeout(() => document.getElementById('editPopup').classList.add('active'), 10);
        document.body.style.overflow = 'hidden';
    };
    </script>
</body>
</html>
