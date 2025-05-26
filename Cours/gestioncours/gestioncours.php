<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Cours</title>
    <link rel="stylesheet" href="/composant/sidebar.css">
    <link rel="stylesheet" href="gestioncours.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="courses-container">
        <?php include_once '../../composant/sidebar2.php'; ?>
        <h1 class="page-title">Gestion des Cours</h1>
        <button id="addCourseButton" class="add-course-button">Ajouter un cours</button>

        <div class="courses-list" id="coursesContainer">
            <!-- Les cours seront chargés dynamiquement par JS -->
        </div>

        <!-- Modal d'ajout -->
        <div id="addCourseModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span id="closeModal" class="close-button">&times;</span>
                <h2 class="modal-title">Ajouter un nouveau cours</h2>
                <form class="add-course-form" method="POST" action="gestioncours_crud.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titre">Titre</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="contenu">Contenu</label>
                        <textarea id="contenu" name="contenu" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status" required>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="files">Fichiers</label>
                        <input type="file" id="files" name="files[]" multiple>
                    </div>
                    <input type="hidden" name="action" value="create">
                    <button type="submit" class="create-button">Ajouter le cours</button>
                </form>
            </div>
        </div>

        <!-- Modal d'édition -->
        <div id="editCourseModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span id="closeEditModal" class="close-button">&times;</span>
                <h2 class="modal-title">Modifier le cours</h2>
                <form id="editCourseForm">
                    <div class="form-group">
                        <label for="editTitre">Titre</label>
                        <input type="text" id="editTitre" required>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editContenu">Contenu</label>
                        <textarea id="editContenu" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="editStatus">Statut</label>
                        <select id="editStatus" required>
                            <option value="active">Actif</option>
                            <option value="inactive">Inactif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editFiles">Fichiers</label>
                        <input type="file" id="editFiles" multiple>
                        <div id="existingFiles" class="existing-files"></div>
                    </div>
                    <button type="submit" class="create-button">Enregistrer les modifications</button>
                </form>
            </div>
        </div>
    </div>

    <script src="gestioncours.js"></script>
    <script>
    // Patch window.editCourse to open the edit modal and fill the form
    window.editCourse = function(course) {
        // Open modal
        const editCourseModal = document.getElementById('editCourseModal');
        editCourseModal.style.display = 'block';
        setTimeout(() => document.body.style.overflow = 'hidden', 10);
        // Fill form fields
        document.getElementById('editTitre').value = course.titre;
        document.getElementById('editDescription').value = course.description;
        document.getElementById('editContenu').value = course.contenu;
        document.getElementById('editStatus').value = course.status;
        // Store course id for update
        editCourseModal.dataset.courseId = course.id;
    };
    // Handle update form submit
    const editForm = document.getElementById('editCourseForm');
    if (editForm) {
        editForm.onsubmit = function(e) {
            e.preventDefault();
            const editCourseModal = document.getElementById('editCourseModal');
            const id = editCourseModal.dataset.courseId;
            const titre = document.getElementById('editTitre').value.trim();
            const description = document.getElementById('editDescription').value.trim();
            const contenu = document.getElementById('editContenu').value.trim();
            const status = document.getElementById('editStatus').value;
            const filesInput = document.getElementById('editFiles');
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('id', id);
            formData.append('titre', titre);
            formData.append('description', description);
            formData.append('contenu', contenu);
            formData.append('status', status);
            if (filesInput && filesInput.files.length > 0) {
                for (let i = 0; i < filesInput.files.length; i++) {
                    formData.append('files[]', filesInput.files[i]);
                }
            }
            fetch('gestioncours_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erreur modification: ' + (data.error || ''));
                }
            })
            .catch(() => alert('Erreur réseau lors de la modification du cours.'));
        };
    }
    // Close modal logic
    const closeEditModal = document.getElementById('closeEditModal');
    if (closeEditModal) {
        closeEditModal.onclick = function() {
            const editCourseModal = document.getElementById('editCourseModal');
            editCourseModal.style.display = 'none';
            document.body.style.overflow = '';
        };
    }
    document.addEventListener('DOMContentLoaded', function () {
        const coursesContainer = document.getElementById('coursesContainer');
        // Load courses via AJAX
        fetch('gestioncours_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=read'
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                coursesContainer.innerHTML = '';
                data.courses.forEach(function(course) {
                    const card = document.createElement('div');
                    card.className = 'course-card-container';
                    card.innerHTML = `
                        <div class='course-card-link' style="cursor:pointer;" data-id='${course.id}'>
                            <div class='course-card'>
                                <h2 class='course-title'>${course.titre}</h2>
                                <p class='course-description'>${course.description}</p>
                                <p class='course-date'>Date de publication : ${course.created_at ? course.created_at.split(' ')[0].split('-').reverse().join('/') : ''}</p>
                                <div class='course-actions'>
                                    <button class='edit-button'>Modifier</button>
                                    <button class='delete-button'>Supprimer</button>
                                </div>
                            </div>
                        </div>
                    `;
                    // Click on card to go to show.php
                    card.querySelector('.course-card-link').onclick = function(e) {
                        // Prevent click if edit/delete button is clicked
                        if (e.target.classList.contains('edit-button') || e.target.classList.contains('delete-button')) return;
                        window.location.href = 'show.php?id=' + course.id;
                    };
                    card.querySelector('.edit-button').onclick = function(e) {
                        e.stopPropagation();
                        window.editCourse(course);
                    };
                    card.querySelector('.delete-button').onclick = function(e) {
                        e.stopPropagation();
                        if (confirm('Supprimer ce cours ?')) {
                            fetch('gestioncours_crud.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: 'action=delete&id=' + encodeURIComponent(course.id)
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.success) location.reload();
                                else alert('Erreur suppression');
                            });
                        }
                    };
                    coursesContainer.appendChild(card);
                });
            }
        });
    });
    </script>
</body>
</html>
