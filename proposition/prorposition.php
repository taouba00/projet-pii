<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /freelance/login/login.php');
    exit;
}

require_once '../login/db.php';

// Get project details if project_id is provided
$project = null;
if (isset($_GET['project_id'])) {
    $project_id = intval($_GET['project_id']);
    $stmt = $conn->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->bind_param('i', $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Propositions</title>
    <script>
        // Define all functions before they're used
        function toggleForm() {
            const form = document.getElementById("proposition-form");
            const overlay = document.getElementById("overlay");
            form.style.display = form.style.display === "none" ? "block" : "none";
            overlay.style.display = overlay.style.display === "none" ? "block" : "none";
        }

        function closeForm() {
            const form = document.getElementById("proposition-form");
            const overlay = document.getElementById("overlay");
            form.style.display = "none";
            overlay.style.display = "none";
            document.getElementById("fakeCreateForm").reset();
        }

        function openEditPopup(proposition) {
            document.getElementById('editPopup').style.display = 'block';
            document.getElementById("overlay").style.display = "block";
            
            document.getElementById('editContenu').value = proposition.contenu;
            document.getElementById('editBudget').value = proposition.budget;
            document.getElementById('editDateCreation').value = proposition.date_creation;
            document.getElementById('editDateFin').value = proposition.date_fin;
            editId = proposition.id;
        }

        function closeEditPopup() {
            document.getElementById('editPopup').style.display = 'none';
            document.getElementById("overlay").style.display = "none";
            editId = null;
        }

        let currentPage = 1;
        const propositionsPerPage = 2;
        let filteredPropositions = [];
        let editId = null;

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

        function handleDeleteClick(button) {
            if(!confirm("Voulez-vous vraiment supprimer cette proposition ?")) return;

            const card = button.closest('.proposition-card');
            const id = card.dataset.id;

            fetch('proposition_crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&id=${id}`
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    loadPropositions();
                } else {
                    alert('Erreur lors de la suppression de la proposition');
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('Erreur lors de la suppression de la proposition');
            });
        }

        function displayPropositions() {
            const startIndex = (currentPage - 1) * propositionsPerPage;
            const endIndex = startIndex + propositionsPerPage;

            document.querySelectorAll('.proposition-card').forEach(function(card) {
                card.style.display = 'none';
            });

            filteredPropositions.slice(startIndex, endIndex).forEach(function(card) {
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
                pageNumbers.textContent = `Page ${currentPage} sur ${totalPages || 1}`;
            }

            if (prevButton) prevButton.disabled = currentPage === 1;
            if (nextButton) nextButton.disabled = currentPage === totalPages || totalPages === 0;
        }

        function performSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            filteredPropositions = Array.from(document.querySelectorAll('.proposition-card')).filter(function(card) {
                const contenu = card.querySelector('.proposition-contenu')?.textContent.toLowerCase() || "";
                const budget = card.querySelector('.proposition-budget')?.textContent || "";
                return contenu.includes(searchTerm) || budget.includes(searchTerm);
            });

            updateSearchResultsCounter();
            currentPage = 1;
            displayPropositions();
        }

        function updateSearchResultsCounter() {
            const counter = document.querySelector('.search-results-counter');
            if (counter) {
                counter.textContent = `${filteredPropositions.length} résultat(s) trouvé(s)`;
            }
        }

        function loadPropositions() {
            const project_id = <?php echo isset($_GET['project_id']) ? intval($_GET['project_id']) : 'null' ?>;
            fetch(`proposition_crud.php?project_id=${project_id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=read'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    const propositionsList = document.getElementById('propositionsList');
                    propositionsList.innerHTML = '';
                    
                    data.propositions.forEach(function(proposition) {
                        const card = document.createElement('div');
                        card.className = 'proposition-card';
                        card.dataset.id = proposition.id;
                        card.dataset.contenu = proposition.contenu;
                        card.dataset.budget = proposition.budget;
                        card.dataset.date_creation = proposition.date_creation;
                        card.dataset.date_fin = proposition.date_fin;
                    card.innerHTML = `
                        <div class="proposition-contenu">${proposition.contenu}</div>
                        <div class="proposition-budget">${proposition.budget} TND</div>
                        <div class="proposition-dates">
                            <div class="date">Début: ${proposition.date_creation}</div>
                            <div class="date">Fin: ${proposition.date_fin}</div>
                        </div>
                        <div class="proposition-timestamp">
                            Créé le: ${new Date(proposition.created_at).toLocaleDateString()}
                        </div>
                        <div class="proposition-actions">
                            <button onclick="handleEditClick(this)">Modifier</button>
                            <button onclick="handleDeleteClick(this)">Supprimer</button>
                            <button onclick="startChat(${proposition.author_id})">Chat</button>
                        </div>
                    `;

                        
                        propositionsList.appendChild(card);
                    });
                    
                    filteredPropositions = Array.from(document.querySelectorAll('.proposition-card'));
                    updateSearchResultsCounter();
                    displayPropositions();
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('Erreur lors du chargement des propositions');
            });
        }
        function startChat(authorId) {
            if (!authorId) return;
            window.location.href = `/freelance/message/chat_start.php?author_id=${authorId}`;
        }

        // Initialize everything when the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            const project_id = <?php echo isset($_GET['project_id']) ? intval($_GET['project_id']) : 'null' ?>;
            
            if (!project_id) {
                alert('Aucun projet sélectionné');
                return;
            }

            // Set up event listeners
            document.getElementById("overlay").addEventListener("click", function() {
                closeForm();
                closeEditPopup();
            });

            document.getElementById('fakeCreateForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = e.target;
                const contenu = form.contenu.value.trim();
                const budget = form.budget.value.trim();
                const date_creation = form.date_creation.value;
                const date_fin = form.date_fin.value;

                if (!contenu || !budget || !date_creation || !date_fin) {
                    alert("Veuillez remplir tous les champs.");
                    return;
                }

                // Send to server
                const formData = new FormData(form);
                formData.append('action', 'create');
                formData.append('project_id', project_id);

                fetch('proposition_crud.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadPropositions();
                        closeForm();
                    } else {
                        alert('Erreur lors de la création de la proposition');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de la création de la proposition');
                });
            });

            document.getElementById('editForm').addEventListener('submit', function(e) {
                e.preventDefault();
                if (!editId) return;

                const formData = new FormData();
                formData.append('action', 'update');
                formData.append('id', editId);
                formData.append('contenu', document.getElementById('editContenu').value);
                formData.append('budget', document.getElementById('editBudget').value);
                formData.append('date_creation', document.getElementById('editDateCreation').value);
                formData.append('date_fin', document.getElementById('editDateFin').value);

                fetch('proposition_crud.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadPropositions();
                        closeEditPopup();
                    } else {
                        alert('Erreur lors de la modification de la proposition');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de la modification de la proposition');
                });
            });

            document.getElementById('prevPage').addEventListener('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    displayPropositions();
                }
            });

            document.getElementById('nextPage').addEventListener('click', function() {
                const totalPages = Math.ceil(filteredPropositions.length / propositionsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    displayPropositions();
                }
            });

            document.getElementById('searchInput').addEventListener('input', performSearch);

            loadPropositions();
        });
    </script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.03'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
            pointer-events: none;
            z-index: -1;
        }

        .propositions-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
        }

        .page-title {
            font-size: 3.5rem;
            font-weight: 800;
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            animation: titleFloat 3s ease-in-out infinite;
        }

        @keyframes titleFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .show-form-button {
            display: block;
            margin: 0 auto 40px;
            padding: 16px 32px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.4);
            position: relative;
            overflow: hidden;
        }

        .show-form-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .show-form-button:hover::before {
            left: 100%;
        }

        .show-form-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 107, 107, 0.6);
        }

        #overlay {
            backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, 0.6);
        }

        .search-container {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        #searchInput {
            width: 100%;
            padding: 16px 24px;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            background: rgba(255, 255, 255, 0.9);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            outline: none;
        }

        #searchInput:focus {
            transform: scale(1.02);
            box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.1), 0 0 0 3px rgba(52, 152, 219, 0.3);
        }

        .search-results-counter {
            margin-top: 12px;
            color: #2c3e50;
            font-weight: 500;
            text-align: center;
            font-size: 0.95rem;
        }

        h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin: 30px 0 20px;
            text-align: center;
            font-weight: 600;
        }

        hr {
            border: none;
            height: 2px;
            background: linear-gradient(90deg, transparent, rgba(44, 62, 80, 0.3), transparent);
            margin: 20px 0;
        }

        .propositions-list {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        }

        .proposition-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .proposition-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
        }

        .proposition-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }

        .proposition-contenu {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
            line-height: 1.4;
        }

        .proposition-budget {
            font-size: 1.8rem;
            font-weight: 800;
            color: #27ae60;
            margin-bottom: 20px;
            position: relative;
        }        .proposition-budget::before {
            content: '💰';
            margin-right: 8px;
        }

        .proposition-dates {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 0.9rem;
            color: #666;
        }

        .proposition-dates .date {
            background: rgba(52, 152, 219, 0.1);
            padding: 5px 10px;
            border-radius: 8px;
        }

        .proposition-timestamp {
            font-size: 0.8rem;
            color: #999;
            text-align: right;
            font-style: italic;
            margin-bottom: 15px;
        }

        .proposition-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .proposition-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            font-size: 0.9rem;
        }

        .proposition-actions button:first-child {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .proposition-actions button:last-child {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .proposition-actions button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }        #proposition-form, #editPopup {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90vw;
            animation: popupSlide 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
        }

        @keyframes popupSlide {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }

        #proposition-form button[style*="float:right"], #editPopup button[style*="float:right"] {
            background: #e74c3c;
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #proposition-form button[style*="float:right"]:hover, #editPopup button[style*="float:right"]:hover {
            background: #c0392b;
            transform: rotate(90deg);
        }

        form label {
            display: block;
            margin: 20px 0 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        form input, form textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fff;
            outline: none;
        }

        form input:focus, form textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: scale(1.02);
        }

        form textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        form button[type="submit"] {
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
            width: 100%;
        }

        form button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.4);
        }

        #paginationControls {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 40px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        #paginationControls button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        #paginationControls button:disabled {
            background: #bdc3c7;
            cursor: not-allowed;
            transform: none;
        }

        #paginationControls button:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
        }

        #pageNumbers {
            color: #2c3e50;
            font-weight: 600;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 15px;
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2.5rem;
            }
            
            .propositions-list {
                grid-template-columns: 1fr;
            }
            
            .proposition-actions {
                justify-content: center;
            }
            
            #proposition-form, #editPopup {
                padding: 25px;
                width: 95vw;
            }
            
            #paginationControls {
                flex-direction: column;
                gap: 15px;
            }
        }

        /* Animations d'entrée */
        .proposition-card {
            animation: cardSlideIn 0.6s ease-out;
        }

        @keyframes cardSlideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Effet de particules subtil */
        .propositions-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 20% 80%, rgba(52, 152, 219, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(44, 62, 80, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Style pour les formulaires amélioré */
        form h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
        }

        .project-info {
            margin-bottom: 40px;
            text-align: center;
        }

        .project-details {
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 15px;
            margin-top: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .project-description {
            margin-bottom: 15px;
            font-size: 1.1rem;
            color: #34495e;
            line-height: 1.4;
        }

        .project-dates, .project-status {
            font-size: 0.9rem;
            color: #7f8c8d;
        }
    </style>
    </script>
</head>
<body>
        <?php include_once '../composant/sidebar.php'; ?>
    <div class="propositions-container">        <?php if ($project): ?>
        <div class="project-info">
            <h1 class="page-title">Propositions pour: <?= htmlspecialchars($project['name']) ?></h1>
        </div>
        <?php else: ?>        <h1 class="page-title">Vos Propositions</h1>
        <?php endif; ?>       
        
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'freelancer'): ?>
        <button type="button" class="show-form-button" onclick="toggleForm()">Faire une proposition</button>
        <?php endif; ?>

        <div id="overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:10;"></div>

        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Rechercher par contenu ou budget...">
            <div class="search-results-counter"></div>
        </div>

        <!-- Formulaire ajout proposition -->        <div id="proposition-form" style="display: none; position:fixed; background:#fff; padding:10px; border:1px solid #000; z-index:20; top:50%; left:50%; transform: translate(-50%, -50%);">
            <button type="button" class="close-button" onclick="closeForm()" style="float:right;">&times;</button>
            <form id="fakeCreateForm">
                <label>Contenu :</label><br>
                <textarea name="contenu" required></textarea><br>
                <label>Budget (TND) :</label><br>
                <input type="number" name="budget" required><br>
                <label>Date de début :</label><br>
                <input type="date" name="date_creation" required><br>
                <label>Date de livraison :</label><br>
                <input type="date" name="date_fin" required><br>
                <button type="submit">Soumettre</button>
            </form>
        </div>

            <!-- Liste des propositions -->
        <div class="propositions-list" id="propositionsList">
            <!-- Les propositions seront chargées dynamiquement ici -->
        </div>
        </div>

        <!-- Pagination -->
        <div id="paginationControls">
            <button id="prevPage">Précédent</button>
            <span id="pageNumbers"></span>
            <button id="nextPage">Suivant</button>
        </div>

        <!-- Popup édition -->
        <div id="editPopup" style="display:none; position:fixed; background:#fff; padding:10px; border:1px solid #000; z-index:30; top:50%; left:50%; transform: translate(-50%, -50%);">
            <button onclick="closeEditPopup()" style="float:right;">&times;</button>
            <h2>Modifier la Proposition</h2>
            <form id="editForm">
                <label>Contenu :</label><br>
                <textarea id="editContenu" name="contenu" required></textarea><br>
                <label>Budget (TND) :</label><br>
                <input type="number" id="editBudget" name="budget" required><br>
                <label>Date de début :</label><br>
                <input type="date" id="editDateCreation" name="date_creation" required><br>
                <label>Date de livraison :</label><br>
                <input type="date" id="editDateFin" name="date_fin" required><br>
                <button type="submit">Enregistrer</button>
            </form>
        </div>
    </div>    
</body>
</html>