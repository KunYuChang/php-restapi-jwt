<?php

class Auth
{
    private int $user_id;

    public function __construct(private UserGateway $user_gateway,
                                private JWTCodec $codec)
    {        
    }

    public function authenticateAPIKey(): bool
    {
        // API KEY 檢查，不符合 400
        if (empty($_SERVER["HTTP_X_API_KEY"])) {

            http_response_code(400);
            echo json_encode(["message" => "missing API key"]);
            return false;
        }

        $api_key = $_SERVER["HTTP_X_API_KEY"];
        $user = $this->user_gateway->getByAPIKey($api_key);

        // API KEY 是否合法，不符合 401
        if ($user === false) {

            http_response_code(401);
            echo json_encode(["message" => "invalid API key"]);
            return false;        

        }

        // 驗證成功
        $this->user_id = $user["id"];

        return true;
    }

    public function getUserID(): int
    {
        return $this->user_id;
    }

    public function authenticateAccessToken(): bool
    {
        // 確認認證格式
        if ( ! preg_match("/^Bearer\s+(.*)$/", $_SERVER["HTTP_AUTHORIZATION"], $matches))
        {
            http_response_code(400);
            echo json_encode(["message" => "incomplete authorization header"]);
            return false;
        }

        // 解析JWT
        try {

            $data = $this->codec->decode($matches[1]);

        } catch (InvalidSignatureException) {

            http_response_code(401);
            echo json_encode(["message" => "invalid signature"]);
            return false;

        } catch (TokenExpiredException) {

            http_response_code(401);
            echo json_encode(["message" => "token has expired"]);
            return false;

        } catch (Exception $e) {

            http_response_code(400);
            echo json_encode(["message" => $e->getMessage()]);
            return false;

        }
        
        // 儲存身份
        $this->user_id = $data["sub"];

        // 認證成功
        return true;
    }
}