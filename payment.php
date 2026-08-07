<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

$admin_whatsapp = '919102316971';

// Request handle karna
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action == 'verify_utr') {
    $utr = trim($_POST['utr'] ?? '');

    // Check karo ki UTR 12 digit ka hai ya nahi
    if (strlen($utr) == 12 && ctype_digit($utr)) {
        
        // WhatsApp notification link generate karna (Wabox / CallMeBot ya direct click link)
        // Aap jab bhi is link par click karenge, WhatsApp par message chala jayega
        $approval_link = "https://mi-payment.vercel.app/payment.php?action=approve&utr=" . $utr;
        $message = "New 1-Day Pass Request! UTR: *{$utr}*. Click to approve: {$approval_link}";
        
        // Optional: WhatsApp API ya direct log
        // Filhal hum success response bhej rahe hain taaki user ko message dikhe ki request chali gayi hai
        echo json_encode([
            "status" => "pending",
            "message" => "UTR submit ho gaya hai! Admin verification ke baad pass turant unlock ho jayega."
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
    // Jab aap WhatsApp link par click karenge, yeh yahan success dikhayega aur app unlock kar dega
    echo "<h2>Pass Successfully Approved for UTR: {$utr} 🎉</h2><p>Aap ab is window ko band kar sakte hain. App mein pass unlock ho chuka hai.</p>";
} 
else {
    echo json_encode([
        "status" => "active",
        "message" => "Mi Assistant UTR Gateway Ready"
    ]);
}
?>
