document.addEventListener('DOMContentLoaded', function () {
    const courses = document.querySelectorAll('.course-card-container');
    const paginationContainer = document.getElementById('pagination');
    const searchInput = document.getElementById('searchInput');
    const searchBadge = document.querySelector('.search-active-badge');
    const searchCounter = document.querySelector('.search-results-counter');

    const coursesPerPage = 3;
    let currentPage = 1;
    let filteredCourses = Array.from(courses);

    function displayVisibleCourses() {
        const startIndex = (currentPage - 1) * coursesPerPage;
        const endIndex = startIndex + coursesPerPage;

        let visibleIndex = 0;
        courses.forEach(course => course.style.display = 'none');

        filteredCourses.forEach((course, index) => {
            if (index >= startIndex && index < endIndex) {
                course.style.display = 'block';
                visibleIndex++;
            }
        });
    }

    function updatePagination() {
        paginationContainer.innerHTML = '';
        const totalPages = Math.ceil(filteredCourses.length / coursesPerPage);
        if (totalPages <= 1) return;

        if (currentPage > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.textContent = 'Précédent';
            prevBtn.addEventListener('click', () => {
                currentPage--;
                displayVisibleCourses();
                updatePagination();
            });
            paginationContainer.appendChild(prevBtn);
        }

        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.textContent = i;
            if (i === currentPage) {
                pageBtn.classList.add('active');
            }
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                displayVisibleCourses();
                updatePagination();
            });
            paginationContainer.appendChild(pageBtn);
        }

        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Suivant';
            nextBtn.addEventListener('click', () => {
                currentPage++;
                displayVisibleCourses();
                updatePagination();
            });
            paginationContainer.appendChild(nextBtn);
        }
    }

    function performSearch() {
        const query = searchInput.value.toLowerCase().trim();

        if (query.length > 0) {
            filteredCourses = Array.from(courses).filter(course => {
                const title = course.querySelector('.course-title').textContent.toLowerCase();
                const description = course.querySelector('.course-description').textContent.toLowerCase();
                return title.includes(query) || description.includes(query);
            });
            searchBadge.style.display = 'inline-block';
            searchCounter.textContent = `${filteredCourses.length} résultat(s)`;
        } else {
            filteredCourses = Array.from(courses);
            searchBadge.style.display = 'none';
            searchCounter.textContent = '';
        }

        currentPage = 1;
        displayVisibleCourses();
        updatePagination();
    }

    searchInput.addEventListener('input', performSearch);

    performSearch();
    
});
