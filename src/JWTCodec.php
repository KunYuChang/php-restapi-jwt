<?php

class JWTCodec
{
    public function __construct(private string $key)
    {}

    public function encode(array $payload): string
    {
        // header
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);
        $header = $this->base64urlEncode($header);

        // payload
        $payload = json_encode($payload);
        $payload = $this->base64urlEncode($payload);

        // signature (256-bit key generator)
        $signature = hash_hmac("sha256",
                               $header . "." . $payload,
                               $this->key,
                               true);

        
        $signature = $this->base64urlEncode($signature);

        return $header . "." . $payload . "." . $signature;
    }

    public function decode(string $token): array
    {
        // 正規表達驗證
        if (preg_match("/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/", $token, $matches) !== 1) {
            throw new InvalidArgumentException("invalid token format");
        }

        // 依據 payload 重新生成簽章
        $signature = hash_hmac("sha256",
                                $matches["header"] . "." . $matches["payload"],
                                $this->key,
                                true);

        // 取得 token 所解析出來的簽章
        $signature_from_token = $this->base64urlDecode($matches["signature"]);

        // 互相比對，以確認資料沒有被串改
        if ( ! hash_equals($signature, $signature_from_token)) {
            // throw new Exception("signature doesn't match");
            throw new InvalidSignatureException($signature . "!=" . $signature_from_token);
        }

        // 取得證明使用者身分的辨識資料
        $payload = json_decode($this->base64urlDecode($matches["payload"]), true);

        if ($payload["exp"] < time()) {
            throw new TokenExpiredException;
        }

        return $payload;
    }

    private function base64urlEncode(string $text): string
    {
        return str_replace(
            ["+", "/" , "="],
            ["-", "_", ""],
            base64_encode($text)
        );
    }

    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ["-", "_"],
            ["+", "/"],
            $text)
        );
    }
}