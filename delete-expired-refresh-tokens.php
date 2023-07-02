<?php

/**
 * 定期清除過期 token (corntab) 
 */

// 嚴格模式
declare(strict_types=1);

require dirname(__DIR__) . "/vendor/autoload.php";

// 載入 ENV
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// 資料庫連線
$database = new Database($_ENV["DB_HOST"],
                         $_ENV["DB_NAME"],
                         $_ENV["DB_USER"],
                         $_ENV["DB_PASS"]);

// 確認 refresh token 是否正確
$refresh_token_gateway = new RefreshTokenGateway($database, $_ENV["SECRET_KEY"]);

echo $refresh_token_gateway->deleteExpired(), '\n';