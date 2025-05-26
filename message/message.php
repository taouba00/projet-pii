<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chat UI</title>
    <link rel="stylesheet" href="./message.css">
    <link rel="stylesheet" href="../composant/sidebar.css"> 
    <style>
        .messenger-container { display: flex; height: 100vh; }
        .user-list { width: 25%; padding: 1rem; border-right: 1px solid #ccc; overflow-y: auto; }
        .chat-section { flex: 1; display: flex; flex-direction: column; }
        .chat-header, .chat-footer { padding: 1rem; border-bottom: 1px solid #ccc; }
        .chat-messages { flex: 1; padding: 1rem; overflow-y: auto; }
        .message { margin: 0.5rem 0; }
        .message.sent {
    background: #d1f7c4;
    color: #222;
    align-self: flex-end;
    border-radius: 18px 18px 4px 18px;
    padding: 10px 18px;
    margin-left: 40px;
    margin-right: 0;
    max-width: 60%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
.message.received {
    background: #f0f0f0;
    color: #222;
    align-self: flex-start;
    border-radius: 18px 18px 18px 4px;
    padding: 10px 18px;
    margin-right: 40px;
    margin-left: 0;
    max-width: 60%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.07);
}
.chat-messages {
    display: flex;
    flex-direction: column;
}
        .message-actions button { margin-left: 0.5rem; }
    </style>
</head>
<body>
<?php include_once '../composant/sidebar.php'; ?>
<div class="messenger-container">


    <!-- Liste des chats -->
    <div class="user-list" id="chat-list">
        <h4>Vos conversations</h4>
        <!-- Chats will be loaded here -->
    </div>

    <!-- Section de chat -->
    <div class="chat-section">
        <div class="chat-header" id="chat-header">
            <span id="chat-title">Sélectionnez une conversation</span>
        </div>

        <div class="chat-messages" id="chat-messages">
            <div class="empty-state">
                <p>Commencez une conversation</p>
            </div>
        </div>

        <div class="chat-footer">
            <input type="text" id="message-input" placeholder="Écrire un message..." disabled>
            <button id="send-message-button" disabled>Envoyer</button>
        </div>
    </div>

</div>


<!-- Show chat creation error if present -->
<?php if (isset($_GET['chat_error'])): ?>
    <div style="color: red; text-align: center; margin: 1em 0;">
        <?= htmlspecialchars(urldecode($_GET['chat_error'])) ?>
    </div>
<?php endif; ?>

<script>
// --- Chat UI Logic ---
let currentChatId = null;
let chats = [];
let userId = null;

// Fetch user id from PHP session
userId = <?php echo json_encode(isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null); ?>;

// Load chats for the logged-in user
function loadChats() {
    fetch('message_api.php?action=get_chats')
        .then(r => r.json())
        .then(data => {
            console.log('Chatter data:', data);
            if (data.success) {
                chats = data.chats;
                renderChatList();
            } else {
                document.getElementById('chat-list').innerHTML += '<div style="color:red">Erreur chargement des conversations</div>';
            }
        });
}

function renderChatList() {
    const chatList = document.getElementById('chat-list');
    chatList.innerHTML = '<h4>Vos conversations</h4>';
    if (!chats.length) {
        chatList.innerHTML += '<div>Aucune conversation</div>';
        return;
    }
    chats.forEach(chat => {
        const other = (chat.user1_id == userId) ? chat.user2 : chat.user1;
        const chatDiv = document.createElement('div');
        chatDiv.className = 'user-item';
        chatDiv.textContent = other.nom + ' ' + other.prenom + ' (' + other.email + ')';
        chatDiv.onclick = () => selectChat(chat.id, other);
        if (chat.id == currentChatId) chatDiv.style.background = '#e0e0e0';
        chatList.appendChild(chatDiv);
    });
}

function selectChat(chatId, otherUser) {
    currentChatId = chatId;
    document.getElementById('chat-title').textContent = 'Chat avec ' + otherUser.nom + ' ' + otherUser.prenom;
    document.getElementById('message-input').disabled = false;
    document.getElementById('send-message-button').disabled = false;
    loadMessages(chatId);
}

function loadMessages(chatId) {
    console.log('Calling loadMessages for chatId:', chatId);
    fetch('message_api.php?action=get_messages&chat_id=' + chatId)
        .then(r => r.json())
        .then(data => {
            console.log('Messages API response:', data);
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
            if (data.success && data.messages.length) {
                data.messages.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = 'message ' + (msg.sender_id == userId ? 'sent' : 'received');
                    div.textContent = msg.contenu || msg.content || '';
                    // Ensure flexbox for alignment
                    div.style.display = 'inline-block';
                    div.style.margin = '4px 0';
                    chatMessages.appendChild(div);
                });
                // Make sure chat-messages uses flex column for alignment
                chatMessages.style.display = 'flex';
                chatMessages.style.flexDirection = 'column';
            } else {
                chatMessages.innerHTML = '<div class="empty-state"><p>Aucun message</p></div>';
            }
        });
}

// Send message
document.addEventListener('DOMContentLoaded', function() {
    loadChats();
    document.getElementById('send-message-button').onclick = function() {
        const input = document.getElementById('message-input');
        const content = input.value.trim();
        if (!content || !currentChatId) return;
        fetch('message_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=send_message&chat_id=' + encodeURIComponent(currentChatId) + '&content=' + encodeURIComponent(content)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                input.value = '';
                loadMessages(currentChatId);
            }
        });
    };
});
</script>

</body>
</html>
