<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="editusers.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <form id="editUserForm" enctype="multipart/form-data">
            <input type="hidden" name="id" id="user_id">
            <div class="form-group">
                <input type="email" name="email" id="email" class="form-control" placeholder=" " required>
                <label for="email">Email</label>
            </div>
            <div class="form-group">
                <input type="password" name="password" id="password" class="form-control" placeholder=" ">
                <label for="password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="nom" id="nom" class="form-control" placeholder=" " required>
                    <label for="nom">Nom</label>
                </div>
                <div class="form-group">
                    <input type="text" name="prenom" id="prenom" class="form-control" placeholder=" " required>
                    <label for="prenom">Prenom</label>
                </div>
            </div>
            <div class="form-group">
                <select name="role" id="role" class="form-control" required>
                    <option value="client">Client</option>
                    <option value="freelancer">Freelancer</option>
                    <option value="admin">Admin</option>
                </select>
                <label for="role">Role</label>
            </div>
            <button type="submit" class="btn btn-primary btn-success-animation">Update User</button>
        </form>
        <div id="update-status" style="margin-top:1em;"></div>
    </div>

    <script>
    // Get user id from URL
    function getUserIdFromUrl() {
        const params = new URLSearchParams(window.location.search);
        return params.get('id');
    }

    // Load user data
    function loadUser() {
        const id = getUserIdFromUrl();
        if (!id) return;
        fetch('user_crud.php?action=read')
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const user = data.users.find(u => u.id == id);
                if (!user) return;
                document.getElementById('user_id').value = user.id;
                document.getElementById('email').value = user.email;
                document.getElementById('nom').value = user.nom;
                document.getElementById('prenom').value = user.prenom;
                document.getElementById('role').value = user.role;
            });
    }

    // Handle update
    document.getElementById('editUserForm').onsubmit = function(e) {
        e.preventDefault();
        const id = document.getElementById('user_id').value;
        const email = document.getElementById('email').value;
        const nom = document.getElementById('nom').value;
        const prenom = document.getElementById('prenom').value;
        const role = document.getElementById('role').value;
        const password = document.getElementById('password').value;
        const formData = new URLSearchParams();
        formData.append('action', 'update');
        formData.append('id', id);
        formData.append('email', email);
        formData.append('nom', nom);
        formData.append('prenom', prenom);
        formData.append('role', role);
        if (password) formData.append('password', password);
        fetch('user_crud.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(r => r.json())
        .then(data => {
            const status = document.getElementById('update-status');
            if (data.success) {
                status.textContent = 'Utilisateur mis à jour avec succès!';
                status.style.color = 'green';
            } else {
                status.textContent = 'Erreur lors de la mise à jour.';
                status.style.color = 'red';
            }
        });
    };

    document.addEventListener('DOMContentLoaded', loadUser);
    </script>
</body>
</html>
