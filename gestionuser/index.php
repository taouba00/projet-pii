<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users</title>
    <link rel="stylesheet" href="affichageusers.css">
        <link rel="stylesheet" href="../composant/sidebar.css"> 
</head>
<body>
    <?php include_once '../composant/sidebar2.php'; ?>
    <div class="users-container">

        <div class="page-header">
            <h1 class="page-title">Users</h1>
        </div>

        <div class="table-responsive">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="users-tbody">
                    <!-- Utilisateurs dynamiques -->
                </tbody>
</div>
<script>
// Dynamically load users and handle actions
function loadUsers() {
    fetch('user_crud.php?action=read')
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('users-tbody');
            tbody.innerHTML = '';
            if (!data.success) {
                tbody.innerHTML = '<tr><td colspan="5" style="color:red">Erreur chargement utilisateurs</td></tr>';
                return;
            }
            data.users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.email}</td>
                    <td>${user.nom}</td>
                    <td>${user.prenom}</td>
                    <td><span class="role-badge">${user.role}</span></td>
                    <td>
                        <div class="action-buttons">
                            <a href="edituser.php?id=${user.id}" class="btn btn-warning">Edit</a>
                            <button class="btn btn-danger" onclick="deleteUser(${user.id})">Delete</button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function deleteUser(id) {
    if (!confirm('Supprimer cet utilisateur ?')) return;
    fetch('user_crud.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete&id=' + encodeURIComponent(id)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) loadUsers();
        else alert('Erreur suppression utilisateur');
    });
}

document.addEventListener('DOMContentLoaded', loadUsers);
</script>
            </table>
        </div>
    </div>
</body>
</html>
