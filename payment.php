<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$telegram_bot_token = '8877149963:AAF4kW52xP59mPq5wzc7KnPKx0gvE79RwNI';
$telegram_chat_id = '8575189303';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. Utr Submit hone par Telegram par buttons ke sath message bhejna
if ($action == 'verify_utr') {
    $utr = trim($_POST['utr'] ?? '');

    if (strlen($utr) == 12 && ctype_digit($utr)) {
        $msg = "🔔 New 1-Day Pass Request!\nUTR: {$utr}\n\nKripya niche diye gaye button se action chunein:";
        
        // Telegram Inline Buttons (Approve & Reject)
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Approve (Confirm)', 'callback_data' => 'approve_' . $utr],
                    ['text' => '❌ Reject', 'callback_data' => 'reject_' . $utr]
                ]
            ]
        ];
        
        $telegram_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage";
        $data = [
            'chat_id' => $telegram_chat_id,
            'text' => $msg,
            'reply_markup' => json_encode($keyboard)
        ];

        // Curl request to send message with buttons
        $ch = curl_init($telegram_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_exec($ch);
        curl_close($ch);
        
        echo json_encode([
            "status" => "pending",
            "message" => "UTR submit ho gaya hai! Admin ko Telegram par buttons bhej diye gaye hain."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Galat UTR! Kripya 12-digit ka asli Transaction ID (UTR) daalein."
        ]);
    }
} 
// 2. Jab aap Telegram par button dabayenge
else if ($action == 'approve') {
    $utr = $_GET['utr'] ?? '';
    echo "<h2 style='color:green;'>Pass Successfully Approved for UTR: {$utr} 🎉</h2><p>Aap ab is window ko band kar sakte hain.</p>";
} 
else if ($action == 'reject') {
    $utr = $_GET['utr'] ?? '';
    echo "<h2 style='color:red;'>Pass Rejected for UTR: {$utr} ❌</h2><p>Yeh payment request radd kar di gayi hai.</p>";
}
else {
    echo json_encode([
        "status" => "active",
        "message" => "Mi Assistant UTR Gateway Ready with Buttons"
    ]);
}
?>
