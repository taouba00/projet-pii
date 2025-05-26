document.addEventListener('DOMContentLoaded', function () {
    const loginBtn = document.getElementById('loginBtn');
    const errorContainer = document.getElementById('error-container');
    const errorList = document.getElementById('error-list');

    loginBtn.addEventListener('click', function () {
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value.trim();
        errorList.innerHTML = '';
        errorContainer.classList.add('d-none');

        let errors = [];

        if (!email) {
            errors.push("Email is required.");
        }
        if (!password) {
            errors.push("Password is required.");
        }

        if (errors.length > 0) {
            errorContainer.classList.remove('d-none');
            errors.forEach(err => {
                const li = document.createElement('li');
                li.textContent = err;
                errorList.appendChild(li);
            });
        } else {
            // AJAX request to login_process.php
            fetch('login_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    window.location.href = '../index.php'; // Redirect on success
                } else {
                    errorContainer.classList.remove('d-none');
                    (data.errors || ['Login failed.']).forEach(err => {
                        const li = document.createElement('li');
                        li.textContent = err;
                        errorList.appendChild(li);
                    });
                }
            })
            .catch(error => {
                console.error('Error during login:', error);
                errorContainer.classList.remove('d-none');
                const li = document.createElement('li');
                li.textContent = 'An error occurred while processing your request. Please try again later.';
                errorList.appendChild(li);
            });
        }
    });
});
