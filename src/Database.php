<?php

class Database
{   
    // PDO 前方加上 ? 使其值可以為 NULL。
    private ?PDO $conn = null;

    // Constructor property promotion
    public function __construct(
        private string $host, 
        private string $name, 
        private string $user, 
        private string $password
    ) {}

    public function getConnection(): PDO
    {
        // 設計 : 避免多次連線

        if ($this->conn === null) {

            $dsn = "mysql:host={$this->host};dbname={$this->name};charset=utf8";

            $this->conn = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false // 是否自動將數字轉成字串
            ]);

        }

        return $this->conn;

    }
}