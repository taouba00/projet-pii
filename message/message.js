function startChat(userName) {
    document.getElementById('chat-title').innerText = userName;
    const messagesContainer = document.getElementById('chat-messages');
    messagesContainer.innerHTML = '';

    const welcomeMsg = document.createElement('div');
    welcomeMsg.classList.add('message', 'received');
    welcomeMsg.textContent = `Vous discutez maintenant avec ${userName}`;
    messagesContainer.appendChild(welcomeMsg);

    // Désactivation (CRUD annulé)
    document.getElementById('message-input').disabled = true;
    document.getElementById('send-message-button').disabled = true;
}

document.getElementById('send-message-button').addEventListener('click', function () {
    alert("Fonction d'envoi désactivée (CRUD annulé)");
});

document.getElementById('message-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        alert("Fonction d'envoi désactivée (CRUD annulé)");
    }
});
