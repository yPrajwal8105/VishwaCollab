<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conversations = [];
$seniors = [];
$current_conversation = null;
$messages = [];

if (isDatabaseAvailable()) {
    // Check if alumni_students table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'alumni_students'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        // Get seniors/alumni available for chat
        $seniors_query = "SELECT u.id, u.name, u.email, a.current_company, a.current_position, a.expertise_areas
                          FROM users u
                          LEFT JOIN alumni_students a ON u.id = a.user_id
                          WHERE u.role = 'student' AND (a.is_available_for_chat = 1 OR a.is_available_for_chat IS NULL)
                          AND u.id != ?
                          ORDER BY u.name ASC";
        $stmt = $conn->prepare($seniors_query);
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $seniors[] = $row;
            }
            $stmt->close();
        }
    } else {
        // Fallback: show all other students if alumni table doesn't exist
        $seniors_query = "SELECT u.id, u.name, u.email, '' as current_company, '' as current_position, '' as expertise_areas
                          FROM users u
                          WHERE u.role = 'student' AND u.id != ?
                          ORDER BY u.name ASC
                          LIMIT 10";
        $stmt = $conn->prepare($seniors_query);
        if ($stmt) {
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $seniors[] = $row;
            }
            $stmt->close();
        }
    }

    // Get conversations (check if table exists)
    $tableCheck = $conn->query("SHOW TABLES LIKE 'chat_conversations'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $conv_query = "SELECT c.*, 
                              CASE WHEN c.junior_id = ? THEN u2.name ELSE u1.name END as other_user_name,
                              CASE WHEN c.junior_id = ? THEN u2.id ELSE u1.id END as other_user_id,
                              (SELECT message FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message,
                              (SELECT created_at FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message_time
                       FROM chat_conversations c
                       LEFT JOIN users u1 ON u1.id = c.junior_id
                       LEFT JOIN users u2 ON u2.id = c.senior_id
                       WHERE c.junior_id = ? OR c.senior_id = ?
                       ORDER BY c.updated_at DESC";
        $stmt = $conn->prepare($conv_query);
        if ($stmt) {
            $stmt->bind_param('iiii', $user_id, $user_id, $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $conversations[] = $row;
            }
            $stmt->close();
        }
    }

    // Get messages for current conversation
    $conversation_id = $_GET['conversation_id'] ?? null;
    if ($conversation_id) {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'chat_messages'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $msg_query = "SELECT m.*, u.name as sender_name 
                          FROM chat_messages m
                          JOIN users u ON u.id = m.sender_id
                          WHERE m.conversation_id = ?
                          ORDER BY m.created_at ASC";
            $stmt = $conn->prepare($msg_query);
            if ($stmt) {
                $stmt->bind_param('i', $conversation_id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $messages[] = $row;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Seniors - Student Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .chat-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1rem;
            height: calc(100vh - 200px);
            margin-top: 2rem;
        }
        .chat-sidebar {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 16px 16px 0 0;
        }
        .chat-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .seniors-list, .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .senior-item, .conversation-item {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .senior-item:hover, .conversation-item:hover {
            background: rgba(26, 115, 232, 0.05);
            border-color: rgba(26, 115, 232, 0.2);
        }
        .senior-item.active, .conversation-item.active {
            background: rgba(26, 115, 232, 0.1);
            border-color: var(--primary-color);
        }
        .senior-name {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.25rem;
        }
        .senior-info {
            font-size: 0.85rem;
            color: var(--muted);
        }
        .chat-main {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background: #f8f9fa;
        }
        .message {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }
        .message.own {
            align-items: flex-end;
        }
        .message.other {
            align-items: flex-start;
        }
        .message-bubble {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            word-wrap: break-word;
        }
        .message.own .message-bubble {
            background: var(--primary-color);
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message.other .message-bubble {
            background: white;
            color: var(--text);
            border-bottom-left-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
        .message-sender {
            font-size: 0.75rem;
            color: var(--muted);
            margin-bottom: 0.25rem;
        }
        .message-time {
            font-size: 0.7rem;
            color: var(--muted);
            margin-top: 0.25rem;
        }
        .chat-input-area {
            padding: 1rem;
            border-top: 1px solid var(--border);
            background: white;
        }
        .chat-input-form {
            display: flex;
            gap: 0.5rem;
        }
        .chat-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 24px;
            font-size: 1rem;
            resize: none;
        }
        .chat-input:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        .btn-send {
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 24px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-send:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--muted);
            text-align: center;
            padding: 2rem;
        }
        .empty-chat i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
        @media (max-width: 960px) {
            .chat-container {
                grid-template-columns: 1fr;
                height: auto;
            }
            .chat-sidebar {
                max-height: 300px;
            }
        }
    </style>
</head>
<body class="theme-student">
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>Student Dashboard</h2>
            </div>
            <div class="sidebar-menu">
                <a href="student-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="student-jobs.php"><i class="fas fa-briefcase"></i> Job Search</a>
                <a href="student-applications.php"><i class="fas fa-file-alt"></i> Applications</a>
                <a href="student-interviews.php"><i class="fas fa-calendar-alt"></i> Interviews</a>
                <a href="student-resume.php"><i class="fas fa-file-pdf"></i> Resume Builder</a>
                <a href="student-chat.php" class="active"><i class="fas fa-comments"></i> Chat with Seniors</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
            <div class="page-header-section">
                <div>
                    <h1 class="page-title">Chat with Seniors</h1>
                    <p class="page-subtitle">Connect with alumni and get guidance on your career journey</p>
                </div>
            </div>

            <div class="chat-container">
                <div class="chat-sidebar">
                    <div class="chat-header">
                        <h3><i class="fas fa-users"></i> Available Seniors</h3>
                    </div>
                    <div class="seniors-list">
                        <?php if (empty($seniors)): ?>
                            <p style="color: var(--muted); text-align: center; padding: 2rem;">No seniors available at the moment</p>
                        <?php else: ?>
                            <?php foreach ($seniors as $senior): ?>
                                <div class="senior-item" onclick="startConversation(<?php echo $senior['id']; ?>)">
                                    <div class="senior-name"><?php echo htmlspecialchars($senior['name']); ?></div>
                                    <div class="senior-info">
                                        <?php if (!empty($senior['current_position'])): ?>
                                            <?php echo htmlspecialchars($senior['current_position']); ?>
                                            <?php if (!empty($senior['current_company'])): ?>
                                                @ <?php echo htmlspecialchars($senior['current_company']); ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            Alumni
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="chat-main">
                    <?php if ($conversation_id && !empty($messages)): ?>
                        <div class="chat-messages" id="chatMessages">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message <?php echo $msg['sender_id'] == $user_id ? 'own' : 'other'; ?>">
                                    <div class="message-sender"><?php echo htmlspecialchars($msg['sender_name']); ?></div>
                                    <div class="message-bubble"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                                    <div class="message-time"><?php echo date('h:i A', strtotime($msg['created_at'])); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="chat-input-area">
                            <form class="chat-input-form" onsubmit="sendMessage(event, <?php echo $conversation_id; ?>)">
                                <input type="text" class="chat-input" id="messageInput" placeholder="Type your message..." required>
                                <button type="submit" class="btn-send">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="empty-chat">
                            <i class="fas fa-comments"></i>
                            <h3>Select a senior to start chatting</h3>
                            <p>Choose a senior from the list to begin a conversation and get career guidance</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function startConversation(seniorId) {
            window.location.href = 'chat-handler.php?action=start&senior_id=' + seniorId;
        }

        function sendMessage(event, conversationId) {
            event.preventDefault();
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;

            fetch('chat-handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=send&conversation_id=' + conversationId + '&message=' + encodeURIComponent(message)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    location.reload();
                } else {
                    alert('Error sending message: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending message');
            });
        }

        // Auto-scroll to bottom
        const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    </script>
</body>
</html>

