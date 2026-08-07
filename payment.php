<?php
// ==========================================
// 1. APNI DETAILS YAHAN DAALEIN (TESTING KE LIYE)
// ==========================================
$client_id = "YOUR_TEST_CLIENT_ID";         // PhonePe portal se copy karke yahan daalein
$client_secret = "YOUR_TEST_CLIENT_SECRET"; // PhonePe portal se copy karke yahan daalein
$client_version = "1"; 
$grant_type = "client_credentials"; 

// Order ki details
$morderid = "ORDER_" . time(); // Har baar naya order ID banega
$amount = 1900;                // ₹19 (Paise mein 1900)
$redirect_url_from_app = "https://your-website.com/success.php"; // Isko baad mein apni website ke link se badal dena

// App ko JSON format mein jawaab dene ke liye Header set karna
header('Content-Type: application/json');

// ==========================================
// 2. TOKEN NIKALNE WALA CODE (STEP 1)
// ==========================================
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'client_id='.$client_id.'&client_version='.$client_version.'&client_secret='.$client_secret.'&grant_type='.$grant_type,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded'
  ),
));

$response = curl_exec($curl);
curl_close($curl);
$getToken = json_decode($response, true);

// Agar Token mil gaya toh aage badho
if(isset($getToken['access_token']) && $getToken['access_token'] !=''){
    $accessToken = $getToken['access_token'];
    
    // ==========================================
    // 3. PAYMENT LINK (CHECKOUT) BANANE WALA CODE (STEP 2)
    // ==========================================
    $curl2 = curl_init();
    
    // JSON Data jo PhonePe ko bhejna hai
    $payload = array(
        "merchantOrderId" => $morderid,
        "amount" => $amount,
        "expireAfter" => 1200,
        "paymentFlow" => array(
            "type" => "PG_CHECKOUT",
            "message" => "Payment for M.i Assistant 1-Day Pass",
            "merchantUrls" => array(
                "redirectUrl" => $redirect_url_from_app
            )
        )
    );

    curl_setopt_array($curl2, array(
      CURLOPT_URL => 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => json_encode($payload),
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: O-Bearer '.$accessToken
      ),
    ));

    $response2 = curl_exec($curl2);
    curl_close($curl2);
    $getPaymentInfo = json_decode($response2, true);

    // ==========================================
    // 4. ANDROID APP KO LINK BHEJNA (FINAL RESULT)
    // ==========================================
    if(isset($getPaymentInfo['redirectUrl']) && $getPaymentInfo['redirectUrl'] !=''){
        // Agar sab theek raha, toh App ko URL de do
        echo json_encode(array(
            "status" => "success",
            "orderId" => $morderid,
            "payment_url" => $getPaymentInfo['redirectUrl']
        ));
    } else {
        // Agar URL banne mein koi error aaya
        echo json_encode(array(
            "status" => "error", 
            "message" => "Payment link nahi ban paya.", 
            "details" => $getPaymentInfo
        ));
    }

} else {
    // Agar Token nikalne mein error aaya
    echo json_encode(array(
        "status" => "error", 
        "message" => "Token generate nahi hua. Apni Test API details check karein."
    ));
}
?>
