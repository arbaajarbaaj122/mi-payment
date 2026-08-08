<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$telegram_bot_token = '8877149963:AAF4kW52xP59mPq5wzc7KnPKx0gvE79RwNI';
$telegram_chat_id = '8575189303';

// Telegram se aane wale callback queries (button clicks) ko handle karna
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $data = $callback_query['data'];
    $chat_id = $callback_query['message']['chat']['id'];
    $message_id = $callback_query['message']['message_id'];
    
    // Data ko parse karna ( jaise approve_123456 ya reject_123456 )
    $parts = explode('_', $data);
    $action_type = $parts[0] ?? '';
    $utr = $parts[1] ?? '';

    $new_text = $callback_query['message']['text'] . "\n\n";

    if ($action_type == 'approve') {
        $new_text .= "Status: ✅ APPROVED & CONFIRMED";
    } else {
        $new_text .= "Status: ❌ REJECTED";
    }

    // Telegram message se buttons hata kar status update karna
    $url = "https://api.telegram.org/bot{$telegram_bot_token}/editMessageText";
    $post_data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $new_text,
        'reply_markup' => json_encode(['inline_keyboard' => []]) // Buttons remove kar dega
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_exec($ch);
    curl_close($ch);
    
    // Telegram ko response dena ki query process ho gayi
    $answer_url = "https://api.telegram.org/bot{$telegram_bot_token}/answerCallbackQuery?callback_query_id=" . $callback_query['id'];
    @file_get_contents($answer_url);
    exit;
}

// App/Frontend se aane wali request handle karna
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == 'verify_utr') {
    $utr = trim($_POST['utr'] ?? '');

    if (strlen($utr) == 12 && ctype_digit($utr)) {
        $msg = "🔔 New 1-Day Pass Request!\nUTR: {$utr}\n\nKripya action sunein:";
        
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approve', 'callback_data' => 'approve_' . $utr],
                    ['text' => '❌ Reject', 'callback_data' => 'reject_' . $utr]
                ]
            ]
        ];
        
        $telegram_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        $post_data = [
            'chat_id' => $telegram_chat_id,
            'text' => $msg,
            'reply_markup' => json_encode($keyboard)
        ];

        $ch = curl_init($telegram_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_exec($ch);
        curl_close($ch);
        
        echo json_encode([
            "status" => "pending",
            "message" => "UTR submit ho gaya hai!"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Galat UTR! 12-digit ka code dalein."
        ]);
    }
} else {
    echo json_encode([
        "status" => "active",
        "message" => "Gateway Active"
    ]);
}
?>
