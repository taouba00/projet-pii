// Pagination
const projectsContainer = document.getElementById('projects-container');
const paginationContainer = document.getElementById('pagination-container');
let projects = Array.from(document.querySelectorAll('.post-card'));
const itemsPerPage = 4;
let currentPage = 1;

function displayPage(page) {
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const currentPageProjects = projects.slice(startIndex, endIndex);

    projectsContainer.innerHTML = '';
    currentPageProjects.forEach(project => {
        projectsContainer.appendChild(project);
    });

    paginationContainer.innerHTML = '';
    const totalPages = Math.ceil(projects.length / itemsPerPage);
    for (let i = 1; i <= totalPages; i++) {
        const button = document.createElement('button');
        button.classList.add('page-button');
        if (i === page) button.classList.add('active');
        button.textContent = i;
        button.onclick = () => {
            currentPage = i;
            displayPage(currentPage);
        };
        paginationContainer.appendChild(button);
    }
}

displayPage(currentPage);

window.goToPropositions = function(projectId) {
    window.location.href = `proposition/proposition.php?project_id=${projectId}`;
};


// Popup d'édition
function openEditPopup(project) {
    const popup = document.getElementById('editPopup');
    popup.style.display = 'flex';
    setTimeout(() => popup.classList.add('active'), 10);

    document.getElementById('editName').value = project.name;
    document.getElementById('editDescription').value = project.description;
    document.getElementById('editStartDate').value = project.start_date;
    document.getElementById('editEndDate').value = project.end_date || '';
    document.getElementById('editStatus').value = project.status;

    document.body.style.overflow = 'hidden';
}

function closeEditPopup() {
    const popup = document.getElementById('editPopup');
    popup.classList.remove('active');
    setTimeout(() => {
        popup.style.display = 'none';
    }, 300);
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('editPopup');

    popup.addEventListener('click', event => {
        if (event.target === popup) {
            closeEditPopup();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && popup.style.display === 'flex') {
            closeEditPopup();
        }
    });

    document.getElementById('editForm').addEventListener('submit', e => {
        e.preventDefault();
        alert('Modifications enregistrées (hors ligne)');
        closeEditPopup();
    });
});

// Fonction de suppression simple (à adapter selon ta logique)
function deleteProject(id) {
    if (confirm("Voulez-vous vraiment supprimer ce projet ?")) {
        // Suppression simple en supprimant la carte du DOM
        projects = projects.filter(proj => {
            const projId = proj.querySelector('.edit-button').onclick.toString().match(/"id": (\d+)/);
            if (projId) return parseInt(projId[1]) !== id;
            return true;
        });
        displayPage(currentPage);
    }
}
