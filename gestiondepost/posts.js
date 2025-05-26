document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const postsContainer = document.getElementById('postsContainer');
    const postCards = Array.from(document.querySelectorAll('.post-card'));
    const paginationContainer = document.getElementById('pagination');
    const prevPageBtn = document.getElementById('prevPage');
    const nextPageBtn = document.getElementById('nextPage');
    const searchResultsCount = document.getElementById('searchResultsCount');

    const itemsPerPage = 3;
    let currentPage = 1;
    let filteredPosts = postCards;
    let currentPost = null;

    // Vérification que Bootstrap est chargé
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JS n’est pas chargé. Vérifiez l’ordre des scripts dans HTML.');
        return;
    }

    const editModal = new bootstrap.Modal(document.getElementById('editModal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

    function displayPosts() {
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        postCards.forEach(post => post.style.display = 'none');
        filteredPosts.slice(startIndex, endIndex).forEach(post => {
            post.style.display = 'block';
        });

        searchResultsCount.textContent = `${filteredPosts.length} résultat(s) trouvé(s)`;
        updatePagination();
    }

    function updatePagination() {
        const totalPages = Math.ceil(filteredPosts.length / itemsPerPage);
        const paginationList = paginationContainer.querySelector('.pagination');

        while (paginationList.children.length > 2) {
            paginationList.removeChild(paginationList.children[1]);
        }

        for (let i = 1; i <= totalPages; i++) {
            const pageItem = document.createElement('li');
            pageItem.className = `page-item ${i === currentPage ? 'active' : ''}`;

            const pageLink = document.createElement('a');
            pageLink.className = 'page-link';
            pageLink.href = '#';
            pageLink.textContent = i;
            pageLink.dataset.page = i;

            pageLink.addEventListener('click', function (e) {
                e.preventDefault();
                currentPage = parseInt(this.dataset.page);
                displayPosts();
            });

            pageItem.appendChild(pageLink);
            paginationList.insertBefore(pageItem, nextPageBtn);
        }

        prevPageBtn.classList.toggle('disabled', currentPage === 1);
        nextPageBtn.classList.toggle('disabled', currentPage === totalPages || totalPages === 0);
    }

    prevPageBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            displayPosts();
        }
    });

    nextPageBtn.addEventListener('click', function (e) {
        e.preventDefault();
        const totalPages = Math.ceil(filteredPosts.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayPosts();
        }
    });

    searchInput.addEventListener('input', function () {
        const searchTerm = this.value.toLowerCase().trim();
        filteredPosts = postCards.filter(post => post.dataset.title.includes(searchTerm));
        currentPage = 1;
        displayPosts();
    });

    // CRUD: Load posts from DB
    fetch('post_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=read'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            postsContainer.innerHTML = '';
            data.posts.forEach(post => {
                const col = document.createElement('div');
                col.className = 'col-md-4 mb-4 post-card';
                col.dataset.title = post.titre.toLowerCase();
                col.innerHTML = `
                <div class="h-100">
                    <img src="${post.image ? '../' + post.image : 'https://via.placeholder.com/300x150'}" class="card-img-top" alt="Image du post">
                    <div class="card-body">
                        <h5 class="card-title">${post.titre}</h5>
                        <p class="card-text">${post.contenu}</p>
                    </div>
                    <div class="card-footer-modern d-flex justify-content-between align-items-center">
                        <div class="date-badge">
                            <span class="date-icon"><i class="far fa-calendar-alt"></i></span>
                            <span class="date-text">${post.created_at ? post.created_at.split(' ')[0].split('-').reverse().join('/') : ''}</span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" title="Modifier cet article">\n<i class="fas fa-pen"></i><span>Modifier</span>\n</button>
                            <button type="button" class="btn-action btn-delete" title="Supprimer cet article">\n<i class="fas fa-trash"></i><span>Supprimer</span>\n</button>
                        </div>
                    </div>
                </div>`;
                // Attach event listeners for edit and delete
                col.querySelector('.btn-edit').onclick = function() {
                    window.editPost(post);
                };
                col.querySelector('.btn-delete').onclick = function() {
                    window.deletePost(post.id);
                };
                postsContainer.appendChild(col);
            });
        }
    });

    // Add debugging logs to ensure 'action' parameter is passed correctly
    console.log('Initializing CRUD operations');

    // Create post
    const createPostButton = document.querySelector('#postForm button');
    createPostButton.onclick = function(e) {
        e.preventDefault();
        const titre = document.getElementById('titre').value;
        const contenu = document.getElementById('contenu').value;
        const imageInput = document.getElementById('image');
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('titre', titre);
        formData.append('contenu', contenu);
        if (imageInput.files[0]) formData.append('image', imageInput.files[0]);

        console.log('Sending create request with data:', {
            titre,
            contenu,
            image: imageInput.files[0] ? imageInput.files[0].name : 'No image'
        });

        fetch('post_crud.php', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Post created successfully!');
                    location.reload();
                } else {
                    alert('Error creating post: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error during create request:', error);
                alert('An error occurred while creating the post.');
            });
    };

    // Edit post (simple prompt for demo)
    window.editPost = function(post) {
        const titre = prompt('Titre', post.titre);
        if (titre === null) return;
        const contenu = prompt('Contenu', post.contenu);
        if (contenu === null) return;
        fetch('post_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=update&id=${post.id}&titre=${encodeURIComponent(titre)}&contenu=${encodeURIComponent(contenu)}&image=${encodeURIComponent(post.image || '')}`
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erreur modification'); });
    };

    // Delete post
    window.deletePost = function(id) {
        if (!confirm('Supprimer ce post ?')) return;
        fetch('post_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=delete&id=${id}`
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); else alert('Erreur suppression'); });
    };

    displayPosts();
});
