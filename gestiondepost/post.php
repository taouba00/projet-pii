<?php
require_once '../login/db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Posts</title>
    <link rel="stylesheet" href="../composant/sidebar.css"> 
    <link rel="stylesheet" href="post.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include_once '../composant/sidebar.php'; ?>
<div class="container mt-4">
    <h2>Posts</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Post créé avec succès !</div>
    <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger">Erreur lors de la création du post.</div>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'freelancer'): ?>
    <h3>Créer un nouveau post</h3>
    <form id="postForm" enctype="multipart/form-data" method="POST" action="post_crud.php">
        <input type="hidden" id="author_id" name="author_id" value="<?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : ''; ?>">
        <input type="hidden" id="author_id" name="action" value="create">
        <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" id="titre" name="titre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="contenu" class="form-label">Contenu</label>
            <textarea id="contenu" name="contenu" class="form-control" rows="5" required></textarea>
        </div>

        <div class="mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" id="image" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Publier</button>
    </form>
    <?php endif; ?>

    <div class="mt-4">
        <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par titre...">
        <div id="searchResultsCount" class="small text-muted mt-1"></div>
    </div>

    <div class="mt-5">
        <h4>Liste des Posts</h4>
        <div class="row" id="postsContainer">
            <!-- Les posts seront chargés dynamiquement par JS -->
        </div>
    </div>

    <div id="pagination" class="mt-4 d-flex justify-content-center">
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <li class="page-item disabled" id="prevPage">
                    <a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>
                </li>
                <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                <li class="page-item" id="nextPage">
                    <a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="posts.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const postsContainer = document.getElementById('postsContainer');

        function loadPosts() {
            fetch('post_crud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=read'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response from post_crud.php:', data); // Debugging log
                if (data.success) {
                    postsContainer.innerHTML = '';
                    data.posts.forEach(post => {
                        const postElement = document.createElement('div');
                        postElement.className = 'col-md-4 mb-4';
                        postElement.innerHTML = `
                            <div class="card h-100">
                                <img src="${post.image ? '../' + post.image : 'https://via.placeholder.com/300x150'}" class="card-img-top">
                                <div class="card-body">
                                    <h5 class="card-title">${post.titre}</h5>
                                    <p class="card-text">${post.contenu}</p>
                                </div>
                                <div class="card-footer text-muted">
                                    ${post.created_at}
                                </div>
                            </div>
                        `;
                        postsContainer.appendChild(postElement);
                    });
                } else {
                    console.error('Failed to load posts:', data.error);
                }
            })
            .catch(error => console.error('Error fetching posts:', error));
        }        loadPosts();

        const postForm = document.getElementById('postForm');
        if (postForm) {
            postForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(postForm);

                fetch('post_crud.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Post created successfully!');
                        loadPosts();
                        postForm.reset();
                    } else {
                        alert('Error creating post: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => console.error('Error creating post:', error));
            });
        }
    });
</script>
</body>
</html>
