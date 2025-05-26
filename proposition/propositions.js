const propositionsPerPage = 2;
let currentPage = 1;
let filteredPropositions = [];

document.addEventListener("DOMContentLoaded", () => {
    filteredPropositions = Array.from(document.querySelectorAll('.proposition-card'));
    displayPropositions();

    document.getElementById('searchInput')?.addEventListener('input', performSearch);
    document.getElementById('prevPage')?.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            displayPropositions();
        }
    });
    document.getElementById('nextPage')?.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredPropositions.length / propositionsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayPropositions();
        }
    });
});

function displayPropositions() {
    const startIndex = (currentPage - 1) * propositionsPerPage;
    const endIndex = startIndex + propositionsPerPage;

    document.querySelectorAll('.proposition-card').forEach(card => {
        card.style.display = 'none';
    });

    filteredPropositions.slice(startIndex, endIndex).forEach(card => {
        card.style.display = 'block';
    });

    updatePaginationControls();
}

function updatePaginationControls() {
    const totalPages = Math.ceil(filteredPropositions.length / propositionsPerPage);
    const pageNumbers = document.getElementById('pageNumbers');
    const prevButton = document.getElementById('prevPage');
    const nextButton = document.getElementById('nextPage');

    if (pageNumbers) {
        pageNumbers.textContent = `Page ${currentPage} sur ${totalPages}`;
    }

    if (prevButton) prevButton.disabled = currentPage === 1;
    if (nextButton) nextButton.disabled = currentPage === totalPages || totalPages === 0;
}

function performSearch() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();

    filteredPropositions = Array.from(document.querySelectorAll('.proposition-card')).filter(card => {
        const contenu = card.querySelector('.proposition-contenu')?.textContent.toLowerCase();
        const budget = card.querySelector('.proposition-budget')?.textContent;

        return contenu.includes(searchTerm) || budget.includes(searchTerm);
    });

    document.querySelector('.search-results-counter').textContent =
        `${filteredPropositions.length} résultat(s) trouvé(s)`;

    currentPage = 1;
    displayPropositions();
}

function startChat(user1, user2) {
    fetch("/chat", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({
            user1_id: user1,
            user2_id: user2
        })
    }).then(() => {
        window.location.href = '/message';
    });
}

function openEditPopup(proposition) {
    document.getElementById('editPopup').style.display = 'block';
    document.getElementById('editForm').action = `/propositions/${proposition.id}`;
    document.getElementById('editContenu').value = proposition.contenu;
    document.getElementById('editBudget').value = proposition.budget;
    document.getElementById('editDateCreation').value = proposition.date_creation;
    document.getElementById('editDateFin').value = proposition.date_fin;
}

function closeEditPopup() {
    document.getElementById('editPopup').style.display = 'none';
}

// ✅ Nouvelle fonction pour gérer le clic sur le bouton Modifier
function handleEditClick(button) {
    const card = button.closest('.proposition-card');

    const proposition = {
        id: card.dataset.id,
        contenu: card.dataset.contenu,
        budget: card.dataset.budget,
        date_creation: card.dataset.date_creation,
        date_fin: card.dataset.date_fin
    };

    openEditPopup(proposition);
}
