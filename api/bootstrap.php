<?php

require dirname(__DIR__) . "/vendor/autoload.php";

ini_set("display_errors", "On");
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");

// 載入 ENV
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// 設定回傳為JSON
header("Content-type: application/json; charset=UTF-8"); 
