<?php
require_once 'config.php';
require_once 'db_connect.php';

if (!isLoggedIn() || getUserRole() !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

header('Content-Type: application/json');

if ($action === 'start') {
    $senior_id = $_GET['senior_id'] ?? 0;
    
    if (!$senior_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid senior ID']);
        exit();
    }
    
    // Check if conversation exists
    $stmt = $conn->prepare("SELECT id FROM chat_conversations WHERE junior_id = ? AND senior_id = ?");
    $stmt->bind_param('ii', $user_id, $senior_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conversation = $result->fetch_assoc();
    $stmt->close();
    
    if ($conversation) {
        header('Location: student-chat.php?conversation_id=' . $conversation['id']);
        exit();
    }
    
    // Create new conversation
    $stmt = $conn->prepare("INSERT INTO chat_conversations (junior_id, senior_id) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $senior_id);
    if ($stmt->execute()) {
        $conversation_id = $conn->insert_id;
        header('Location: student-chat.php?conversation_id=' . $conversation_id);
        exit();
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create conversation']);
    }
    $stmt->close();
    
} elseif ($action === 'send') {
    $conversation_id = $_POST['conversation_id'] ?? 0;
    $message = $_POST['message'] ?? '';
    
    if (!$conversation_id || !$message) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit();
    }
    
    // Verify user is part of conversation
    $stmt = $conn->prepare("SELECT id FROM chat_conversations WHERE id = ? AND (junior_id = ? OR senior_id = ?)");
    $stmt->bind_param('iii', $conversation_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
    $stmt->close();
    
    // Insert message
    $stmt = $conn->prepare("INSERT INTO chat_messages (conversation_id, sender_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $conversation_id, $user_id, $message);
    if ($stmt->execute()) {
        // Update conversation timestamp
        $conn->query("UPDATE chat_conversations SET updated_at = NOW() WHERE id = $conversation_id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
    $stmt->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>

