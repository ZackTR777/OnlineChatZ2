<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

$dataFile = 'users_accounts.json';
$chatFile = 'chat_messages.json';

// Initialize files if they don't exist
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode(['users' => []]));
}
if (!file_exists($chatFile)) {
    file_put_contents($chatFile, json_encode(['messages' => []]));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'register') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $data = json_decode(file_get_contents($dataFile), true);
    
    // Check if username exists
    foreach ($data['users'] as $user) {
        if ($user['username'] === $username) {
            echo json_encode(['success' => false, 'message' => 'Username already exists']);
            exit;
        }
    }
    
    // Add new user
    $data['users'][] = [
        'username' => $username,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents($dataFile, json_encode($data));
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
    
} elseif ($action === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $data = json_decode(file_get_contents($dataFile), true);
    
    foreach ($data['users'] as $user) {
        if ($user['username'] === $username && password_verify($password, $user['password'])) {
            echo json_encode(['success' => true, 'username' => $username]);
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    
} elseif ($action === 'send_message') {
    $username = $_POST['username'] ?? '';
    $message = $_POST['message'] ?? '';
    
    $chatData = json_decode(file_get_contents($chatFile), true);
    
    $chatData['messages'][] = [
        'username' => $username,
        'message' => htmlspecialchars($message),
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Keep only last 100 messages
    if (count($chatData['messages']) > 100) {
        $chatData['messages'] = array_slice($chatData['messages'], -100);
    }
    
    file_put_contents($chatFile, json_encode($chatData));
    echo json_encode(['success' => true]);
    
} elseif ($action === 'get_messages') {
    $chatData = json_decode(file_get_contents($chatFile), true);
    echo json_encode(['messages' => $chatData['messages']]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
