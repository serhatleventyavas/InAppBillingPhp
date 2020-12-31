<?php
require_once './libs/google/vendor/autoload.php';
header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");

$credentials_file = "./config/client_credentials.json";

$client = new Google_Client();
$client->setAuthConfig($credentials_file);
$client->addScope("https://www.googleapis.com/auth/androidpublisher");
$httpClient = $client->authorize();
$client->refreshTokenWithAssertion();

if (empty($_GET["skuId"]) || empty($_GET["purchaseToken"])) {
    $response["error"] = true;
    $response["message"] = "Bir hata oluştu";
    $response["responseCode"] = 10000;
    $response["data"] = null;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    return;
}

$packageName = "com.wnum.android.virtual_phone_number"; 
$skuId = $_GET["skuId"];
$salesToken = $_GET["purchaseToken"];
$token = $client->getAccessToken();

$accessToken = $token['access_token'];

$url = "https://www.googleapis.com/androidpublisher/v3/applications/" . $packageName . "/purchases/products/" . $skuId . "/tokens/" . $salesToken;
$response = $httpClient->get($url);

$statusCode = $response->getStatusCode();
$body = json_decode($response->getBody(), JSON_UNESCAPED_UNICODE);

$response = ["error" => true, "message" => "", "data" => []];

if ($statusCode != 200 || $body == null) {
    $response["error"] = true;
    $response["message"] = "Bir hata oluştu";
    $response["responseCode"] = 10001;
    $response["data"] = null;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    return;
}

$purchaseState = -1;

if (isset($body["purchaseState"])) {
    $purchaseState = $body["purchaseState"];
}

// purchaseState = -1 is unknown state
// purchaseState = 0 is success
// purchaseState = 1 is cancelled
// purchaseState = 2 is pending

if ($purchaseState == -1 || $purchaseState == 1) {
    $response["error"] = true;
    $response["message"] = "Bir hata oluştu";
    $response["responseCode"] = 10002;
    $response["data"] = null;
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    return;
}

$response["error"] = false;
$response["message"] = "Satış başarılı";
$response["responseCode"] = 200;
$response["data"] = null;
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>