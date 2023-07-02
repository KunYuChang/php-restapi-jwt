<?php

// 嚴格模式
declare(strict_types=1);

require __DIR__ . "/bootstrap.php";

// 資料庫連線
$database     = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PASS"]);
$task_gateway = new TaskGateway($database);
$user_gateway = new UserGateway($database);
$codec        = new JWTCodec($_ENV['SECRET_KEY']);
$auth         = new Auth($user_gateway, $codec);

// 解析 URI
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode("/", $path);
$resource = $parts[2];
$id = $parts[3] ?? null;

// RESOURCE 檢查，不符合 404 
if ($resource != "tasks") {
    // header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    http_response_code(404);
    exit;
}

// Auth (API Key)
// if (! $auth->authenticateAPIKey()) {
//     exit;
// }

// Auth (API Token)
if ( ! $auth->authenticateAccessToken()) {
    exit;
}

$user_id = $auth->getUserID();

$controller = new TaskController($task_gateway, $user_id);
$controller->processRequest($_SERVER['REQUEST_METHOD'], $id);