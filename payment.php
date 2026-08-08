<?php
// CORS aur Headers Setup
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// ==========================================
// 1. AAPKI CONFIGURATION DETAILS (Zaroori)
// ==========================================
$telegram_bot_token = '8877149963:AAF4kW52xP59mPq5wzc7KnPKx0gvE79RwNI';
$telegram_chat_id = '8575189303';
$firebase_project_id = 'meta-library-v07pf'; // Aapka naya Firebase Project ID


// ==========================================
// 2. TELEGRAM BUTTON CLICKS KO HANDLE KARNA (Approve / Reject)
// ==========================================
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update['callback_query'])) {
    $callback_query = $update['callback_query'];
    $data = $callback_query['data'];
    $chat_id = $callback_query['message']['chat']['id'];
    $message_id = $callback_query['message']['message_id'];
    
    // Data ko parse karna (jaise 'approve_123456789012' ya 'reject_123456789012')
    $parts = explode('_', $data);
    $action_type = $parts[0] ?? '';
    $utr = $parts[1] ?? '';

    $new_text = $callback_query['message']['text'] . "\n\n";
    $fb_status = "";

    if ($action_type == 'approve') {
        $new_text .= "Status: ✅ APPROVED & CONFIRMED";
        $fb_status = "approved";
    } else {
        $new_text .= "Status: ❌ REJECTED";
        $fb_status = "rejected";
    }

    // A. Telegram Message Update Karna (Buttons Hatana)
    $url = "https://api.telegram.org/bot{$telegram_bot_token}/editMessageText";
    $post_data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $new_text,
        'reply_markup' => json_encode(['inline_keyboard' => []])
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_exec($ch);
    curl_close($ch);
    
    // B. Naye Firebase (Firestore) Mein Status Update Karna
    $firestore_url = "https://firestore.googleapis.com/v1/projects/{$firebase_project_id}/databases/(default)/documents/payments/{$utr}?updateMask.fieldPaths=status";
    $fb_data = json_encode([
        "fields" => [
            "status" => ["stringValue" => $fb_status]
        ]
    ]);
    
    $ch_fb = curl_init($firestore_url);
    curl_setopt($ch_fb, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch_fb, CURLOPT_POSTFIELDS, $fb_data);
    curl_setopt($ch_fb, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch_fb, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch_fb);
    curl_close($ch_fb);

    // C. Telegram ko bata dena ki button click successfully process ho gaya
    $answer_url = "https://api.telegram.org/bot{$telegram_bot_token}/answerCallbackQuery?callback_query_id=" . $callback_query['id'];
    @file_get_contents($answer_url);
    exit;
}


// ==========================================
// 3. APP SE UTR SUBMIT HONE PAR TELEGRAM KO MESSAGE BHEJNA
// ==========================================
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
        
        echo json_encode(["status" => "pending", "message" => "Telegram par notification bhej diya gaya hai!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid UTR format"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Action"]);
}
?>
