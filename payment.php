<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$telegram_bot_token = '8877149963:AAF4kW52xP59mPq5wzc7KnPKx0gvE79RwNI';
$telegram_chat_id = '8575189303';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == 'verify_utr') {
    $utr = trim($_POST['utr'] ?? '');

    if (strlen($utr) == 12 && ctype_digit($utr)) {
        
        $approval_link = "https://mi-payment.vercel.app/payment.php?action=approve&utr=" . $utr;
        $msg = "New 1-Day Pass Request!\nUTR: {$utr}\n\nClick below to approve:\n{$approval_link}";
        
        // Telegram Bot API URL
        $telegram_url = "https://api.telegram.org/bot{$telegram_bot_token}/sendMessage?chat_id={$telegram_chat_id}&text=" . urlencode($msg);
        @file_get_contents($telegram_url);
        
        echo json_encode([
            "status" => "pending",
            "message" => "UTR submit ho gaya hai! Telegram par notification bhej di gayi hai."
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Galat UTR! Kripya 12-digit ka asli Transaction ID (UTR) daalein."
        ]);
    }
} 
else if ($action == 'approve') {
    $utr = $_GET['utr'] ?? '';
    echo "<h2>Pass Successfully Approved for UTR: {$utr} 🎉</h2><p>Aap ab is window ko band kar sakte hain. App mein pass unlock ho chuka hai.</p>";
} 
else {
    echo json_encode([
        "status" => "active",
        "message" => "Mi Assistant UTR Gateway Ready"
    ]);
}
?>
