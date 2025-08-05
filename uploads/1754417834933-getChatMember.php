<?php

// Get parameters from the request
$botToken = isset($_GET['bot_token']) ? $_GET['bot_token'] : null;
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$chatIds = isset($_GET['chat_id']) ? $_GET['chat_id'] : null;

// Check if any parameter is missing
if (empty($botToken) || empty($userId) || empty($chatIds)) {
    $response = array(
        'status' => 'error',
        'message' => 'Missing parameter list: bot_token, user_id, chat_id'
    );
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Function to check if a user is a member, admin, or creator of a chat
function getUserStatus($botToken, $userId, $chatId)
{
    $apiUrl = "https://api.telegram.org/bot$botToken/getChatMember?chat_id=$chatId&user_id=$userId";

    // Initialize cURL session
    $ch = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for simplicity

    // Execute cURL session and get the response
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        $error = array(
            'status' => 'false',
            'message' => 'Internal error: cURL request failed'
        );
        echo json_encode($error, JSON_PRETTY_PRINT);
        exit;
    }

    // Close cURL session
    curl_close($ch);

    // Decode the response JSON
    $data = json_decode($response, true);

    // Check if the response is valid
    if (!$data || !isset($data['ok']) || !isset($data['result']['status'])) {
        $error = array(
            'status' => 'false',
            'message' => 'Internal error: Failed to get valid response from Telegram API'
        );
        echo json_encode($error, JSON_PRETTY_PRINT);
        exit;
    }

    // Check user status
    $status = $data['result']['status'];

    // Return the user status
    return $status;
}

// Process each chat ID
$chatIdsArray = explode(',', $chatIds);
$joinedChats = [];
$notJoinedChats = [];


foreach ($chatIdsArray as $chatId) {
    $userStatus = getUserStatus($botToken, $userId, $chatId);

    if ($userStatus == 'member' || $userStatus == 'administrator' || $userStatus == 'creator') {
        $joinedChats[] = $chatId;

        
    } else {
        $notJoinedChats[] = $chatId;
    }
}

// Prepare the final response
$response = array(
    'status' => 'true',
    'is_joined' => (empty($notJoinedChats)),
    'joined_chat' => $joinedChats,
    'not_joined_chat' => $notJoinedChats
);

echo json_encode($response, JSON_PRETTY_PRINT);
?>
