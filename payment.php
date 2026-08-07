<?php
header('Content-Type: application/json');

// Aapki Asli Razorpay Test Keys
$key_id = 'rzp_test_TN2oBxZyouy30a';
$key_secret = 'm07IiucUMf4x8cp2wKkNaprc';

// Order Details (₹19.00 = 1900 paisa)
$amount = 1900; 
$receipt_id = 'ORD_' . rand(100000, 999999);

// Razorpay API par Order Create karne ki Request
$url = 'https://api.razorpay.com/v1/orders';
$data = array(
    'amount' => $amount,
    'currency' => 'INR',
    'receipt' => $receipt_id,
    'payment_capture' => 1
);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, "$key_id:$key_secret");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$response = curl_exec($ch);
curl_close($ch);
$order = json_decode($response, true);

if (isset($order['id'])) {
    echo json_encode([
        "status" => "success",
        "order_id" => $order['id'],
        "key" => $key_id,
        "amount" => $amount
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Razorpay Order Create nahi ho paya"
    ]);
}
?>
