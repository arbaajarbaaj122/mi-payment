<?php
// PhonePe Test (Sandbox) API Code
header('Content-Type: application/json');

// 1. PhonePe Test Details (Sandbox)
$merchantId = 'PGTESTPAYUAT';
$saltKey = '099eb0cd-02cf-4e2a-8aca-3e6c6aff0399'; // Official test salt key
$saltIndex = 1;

// 2. Order Details
$orderId = 'ORD' . rand(100000, 999999);
$amount = 1900; // ₹19.00 (PhonePe paisa mein amount leta hai)

// 3. App Success URL 
$successUrl = "https://mi-payment.vercel.app/payment.php?status=success";

// 4. Payload Ready Karna
$payload = [
    "merchantId" => $merchantId,
    "merchantTransactionId" => $orderId,
    "merchantUserId" => "MUID_USER123",
    "amount" => $amount,
    "redirectUrl" => $successUrl,
    "redirectMode" => "REDIRECT",
    "callbackUrl" => $successUrl,
    "mobileNumber" => "9999999999",
    "paymentInstrument" => [
        "type" => "PAY_PAGE"
    ]
];

// 5. Data Encode Karna
$encode = base64_encode(json_encode($payload));

// 6. X-VERIFY Hash Banana (PhonePe Security)
$string = $encode . '/pg/v1/pay' . $saltKey;
$sha256 = hash('sha256', $string);
$xVerify = $sha256 . '###' . $saltIndex;

// 7. cURL Request PhonePe Server Ko Bhejna
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api-preprod.phonepe.com/apis/pg-sandbox/pg/v1/pay",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode(["request" => $encode]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "X-VERIFY: " . $xVerify
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

// 8. App ko JSON bhej do
if ($err) {
    echo json_encode(["status" => "error", "message" => "Connection Error: " . $err]);
} else {
    $result = json_decode($response, true);
    
    // Agar PhonePe ne URL de diya, toh App ko bhej do
    if (isset($result['success']) && $result['success'] == true) {
        $paymentUrl = $result['data']['instrumentResponse']['redirectInfo']['url'];
        echo json_encode([
            "status" => "success",
            "payment_url" => $paymentUrl
        ]);
    } else {
        // Agar PhonePe ne koi error diya 
        echo json_encode([
            "status" => "error",
            "message" => "PhonePe API Error: " . ($result['message'] ?? 'API fail ho gayi')
        ]);
    }
}
?>
