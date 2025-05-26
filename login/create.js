// Affichage du nom du fichier sélectionné
document.getElementById('photo').addEventListener('change', function(e) {
    const fileName = e.target.files[0]?.name || 'No file selected';
    const fileNameDisplay = document.querySelector('.file-name');
    fileNameDisplay.textContent = fileName;
    fileNameDisplay.style.display = 'block';
});

// Indicateur de force du mot de passe
document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.querySelector('.password-strength-bar');

    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^A-Za-z0-9]/)) strength++;

    strengthBar.className = 'password-strength-bar strength-' + strength;
});

// Validation du formulaire et envoi AJAX réel
document.getElementById('signup-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);
    const errorBox = document.getElementById('error-messages');
    const errorList = document.getElementById('error-list');
    errorList.innerHTML = '';
    errorBox.style.display = 'none';

    fetch('create_process.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '../login/login.php'; // Redirect on success
        } else {
            errorBox.style.display = 'block';
            (data.errors || ['Account creation failed.']).forEach(err => {
                const li = document.createElement('li');
                li.textContent = err;
                errorList.appendChild(li);
            });
        }
    })
    .catch(() => {
        errorBox.style.display = 'block';
        const li = document.createElement('li');
        li.textContent = 'An error occurred. Please try again.';
        errorList.appendChild(li);
    });
});
