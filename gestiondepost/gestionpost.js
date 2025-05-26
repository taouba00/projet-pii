document.addEventListener('DOMContentLoaded', function () {
    const postsContainer = document.querySelector('.posts-list');
    const posts = Array.from(postsContainer.children);
    const itemsPerPage = 3;
    const totalPages = Math.ceil(posts.length / itemsPerPage);
    let currentPage = 1;

    function showPage(page) {
        const start = (page - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        posts.forEach(post => post.style.display = 'none');
        for (let i = start; i < end; i++) {
            if (posts[i]) {
                posts[i].style.display = 'block';
            }
        }
    }

    function generatePagination() {
        const paginationContainer = document.getElementById('pagination');
        paginationContainer.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement('button');
            pageButton.innerText = i;
            pageButton.classList.add('page-btn');
            pageButton.addEventListener('click', function () {
                currentPage = i;
                showPage(currentPage);
            });
            paginationContainer.appendChild(pageButton);
        }
    }

    showPage(currentPage);
    generatePagination();

    const searchInput = document.getElementById('searchInput');
    const pagination = document.getElementById('pagination');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = searchInput.value.toLowerCase().trim();
            let hasSearch = searchTerm !== "";

            posts.forEach(post => {
                const titleElement = post.querySelector('.post-title');
                const title = titleElement ? titleElement.textContent.toLowerCase() : "";
                post.style.display = title.includes(searchTerm) ? 'block' : 'none';
            });

            pagination.style.display = hasSearch ? 'none' : 'block';
            if (!hasSearch) showPage(currentPage);
        });
    }

    // Handle form submission for creating a post
    const createPostForm = document.getElementById('createPostForm');
    if (createPostForm) {
        createPostForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(createPostForm);
            formData.append('action', 'create');

            fetch('post_crud.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Post created successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.error || 'An unknown error occurred.'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
        });
    }
});
