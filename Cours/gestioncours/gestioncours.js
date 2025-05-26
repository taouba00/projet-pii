document.addEventListener('DOMContentLoaded', function () {
    const addCourseButton = document.getElementById('addCourseButton');
    const addCourseModal = document.getElementById('addCourseModal');
    const closeModal = document.getElementById('closeModal');

    addCourseButton.addEventListener('click', () => {
        addCourseModal.style.display = 'block';
        setTimeout(() => document.body.style.overflow = 'hidden', 10);
    });

    closeModal.addEventListener('click', () => closeModalFunction(addCourseModal));

    const editButtons = document.querySelectorAll('.edit-button');
    const editCourseModal = document.getElementById('editCourseModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const editForm = document.getElementById('editCourseForm');
    const existingFiles = document.getElementById('existingFiles');

    editButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            document.getElementById('editTitre').value = this.dataset.courseTitle;
            document.getElementById('editDescription').value = this.dataset.courseDescription;
            document.getElementById('editContenu').value = this.dataset.courseContent;
            document.getElementById('editStatus').value = this.dataset.courseStatus;
            existingFiles.innerHTML = ''; // Aucun fichier dynamique ici
            editCourseModal.style.display = 'block';
            setTimeout(() => document.body.style.overflow = 'hidden', 10);
        });
    });

    closeEditModal.addEventListener('click', () => closeModalFunction(editCourseModal));

    function closeModalFunction(modal) {
        modal.style.opacity = '0';
        document.body.style.overflow = '';
        setTimeout(() => {
            modal.style.display = 'none';
            modal.style.opacity = '';
        }, 300);
    }

    window.addEventListener('click', function (event) {
        if (event.target === addCourseModal) closeModalFunction(addCourseModal);
        if (event.target === editCourseModal) closeModalFunction(editCourseModal);
    });

    document.querySelectorAll('.modal-content').forEach(content => {
        content.addEventListener('click', event => event.stopPropagation());
    });

    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.opacity = '0';
            setTimeout(() => successMessage.style.display = 'none', 500);
        }, 5000);
    }

    // Add course creation handler
    const addCourseForm = document.querySelector('.add-course-form');
    if (addCourseForm) {
        addCourseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const titre = document.getElementById('titre').value.trim();
            const description = document.getElementById('description').value.trim();
            const contenu = document.getElementById('contenu').value.trim();
            const status = document.getElementById('status').value;
            const filesInput = document.getElementById('files');
            const formData = new FormData();
            formData.append('action', 'create');
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
                    alert('Erreur création: ' + (data.error || '')); 
                }
            })
            .catch(() => alert('Erreur réseau lors de la création du cours.'));
        });
    }
});
