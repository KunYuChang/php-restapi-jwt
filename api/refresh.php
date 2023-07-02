<?php

declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    
    http_response_code(405);
    header("Allow: POST");
    exit;
}

// request data : json -> array
$data = (array) json_decode(file_get_contents("php://input"), false);

// 檢查 token 是否存在
if ( ! array_key_exists("token", $data)) {
    
    http_response_code(400);
    echo json_encode(["message" => "missing token"]);
    exit;
}

// JWT DECODE 並檢查 TOKEN 時效是否過期
$codec = new JWTCodec($_ENV["SECRET_KEY"]);

try {

    $payload = $codec->decode($data["token"]);

} catch (Exception) {

    http_response_code(400);
    echo json_encode(["message" => "invalid token"]);
    exit;

}

$user_id = $payload["sub"];

// 資料庫連線
$database = new Database($_ENV["DB_HOST"],
                         $_ENV["DB_NAME"],
                         $_ENV["DB_USER"],
                         $_ENV["DB_PASS"]);

// 確認 refresh token 是否正確
$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);
$refresh_token = $refresh_token_gateway->getByToken($data["token"]);

if ($refresh_token === false) {

    http_response_code(401);
    echo json_encode(["message" => "invalid token (not on whitelist"]);
    exit;

}

// 依據 token 取得使用者 
$user_gateway = new UserGateway($database);

$user = $user_gateway->getById($user_id);

if ($user === false) {

    http_response_code(401);
    echo json_encode(["message" => "invalid authentication"]);
    exit;

}

require __DIR__ . "/tokens.php";


$refresh_token_gateway->delete($data["token"]);
$refresh_token_gateway->create($refresh_token, $refresh_token_expiry); // 參數來自於 tokens.php