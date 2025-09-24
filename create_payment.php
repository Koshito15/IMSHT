<?php
// create_payment.php
// POST: amount (in centavos), e.g. 1000 => ₱10.00

$secret_key = "sk_test_psr9m6mXoYfv8NKSopxbhN3Q"; // replace with your secret key
$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid amount']);
    exit;
}

// Redirect URLs (PayMongo will redirect user here after checkout)
$success = "https://randev.wuaze.com/success.php"; // change to your domain
$failed  = "https://randev.wuaze.com/failed.php";

$payload = [
  "data" => [
    "attributes" => [
      "amount" => $amount,
      "currency" => "PHP",
      "redirect" => [
        "success" => $success,
        "failed" => $failed
      ],
      "type" => "gcash"
    ]
  ]
];

$ch = curl_init("https://api.paymongo.com/v1/sources");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Basic " . base64_encode($secret_key . ":")
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    http_response_code(500);
    echo json_encode(['error' => $err]);
} else {
    header('Content-Type: application/json');
    file_put_contents("paymongo_log.txt", $response, FILE_APPEND);
    echo $response;
}
