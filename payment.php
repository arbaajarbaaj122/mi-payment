<?php
header('Content-Type: application/json');

// NOTE: PhonePe Checkout v2 ke liye aapko Test Client ID aur Secret ki zaroorat padegi.
// Agar aapke paas abhi test credentials nahi hain, toh yeh sandbox token error de sakta hai.
$client_id = 'YOUR_TEST_CLIENT_ID';     // Yahan apni Test Client ID daalein
$client_secret = 'YOUR_TEST_CLIENT_SECRET'; // Yahan apna Test Client Secret daalein
$client_version = '1';
$grant_type = 'client_credentials';

// 1. Request OAuth Token
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'client_id='.$client_id.'&client_version='.$client_version.'&client_secret='.$client_secret.'&grant_type='.$grant_type,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/x-www-form-urlencoded'
    ),
]);

$response = curl_exec($curl);
curl_close($curl);
$getToken = json_decode($response, true);

// 2. Validate Token & Create Order
if (isset($getToken['access_token']) && $getToken['access_token'] != '') {
    $accessToken = $getToken['access_token'];
    
    $morderid = 'ORD_' . rand(100000, 999999);
    $successUrl = "https://mi-payment.vercel.app/payment.php?status=success";

    // 3. Checkout v2 Pay Request
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            "merchantOrderId" => $morderid, 
            "amount" => 1900, // ₹19.00 in paisa
            "expireAfter" => 1200, 
            "metaInfo" => [
                "udf1" => "mi_assistant", 
                "udf2" => "1_day_pass"
            ], 
            "paymentFlow" => [
                "type" => "PG_CHECKOUT", 
                "message" => "Pay ₹19 for 1-Day Pass", 
                "merchantUrls" => [
                    "redirectUrl" => $successUrl
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: O-Bearer ' . $accessToken
        ),
    ));

    $responsePay = curl_exec($curl);
    curl_close($curl);
    $getPaymentInfo = json_decode($responsePay, true);

    if (isset($getPaymentInfo['redirectUrl']) && $getPaymentInfo['redirectUrl'] != '') {
        echo json_encode([
            "status" => "success",
            "payment_url" => $getPaymentInfo['redirectUrl']
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Token generate ho gaya, par Checkout URL nahi mila: " . ($getPaymentInfo['message'] ?? 'Unknown error')
        ]);
    }

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Token generate nahi hua. Apni Test Client ID aur Secret check karein."
    ]);
}
?>
