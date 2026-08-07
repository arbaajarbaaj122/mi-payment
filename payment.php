<?php
// PhonePe Bypass - Direct App Unlock Test Code
header('Content-Type: application/json');

// Yeh direct success URL aapki app pakdegi aur premium features unlock kar degi
$successUrl = "https://mi-payment.vercel.app/payment.php?status=success";

echo json_encode([
    "status" => "success",
    "payment_url" => $successUrl
]);
?>
