<?php

class TaskController {
    
    public function __construct(
        private TaskGateway $gateway,
        private int $user_id
    ) {}

    public function processRequest(string $method, ?string $id): void {

        $id = !empty($id) ? $id : null;   

        if ($id === null) {

            if ($method == "GET") {

                echo json_encode($this->gateway->getAllForUser($this->user_id));
            
            } elseif ($method == "POST") {
                
                // 轉成array
                $data = (array) json_decode(file_get_contents("php://input"), true);

                // 資料驗證
                $errors = $this->getValidationErrors($data);
                
                // 伺服器理解請求並且語法正確，但它無法處理。(沒有通過資料驗證)
                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }
                
                $id = $this->gateway->createForUser($this->user_id, $data);
                
                $this->respondCreated($id);


            } else {
                $this->respondMethodNotAllowed("GET, POST");
            }

        // request 有帶 id
        } else {

            // 先判斷request id對應的資料是否存在
            $task = $this->gateway->getForUser($this->user_id, $id);

            if ($task === false) {
                $this->respondNotFound($id);
                return;
            }

            switch ($method) {

                case "GET":
                    echo json_encode($task);
                    break;

                case "PATCH":

                    // 轉成array
                    $data = (array) json_decode(file_get_contents("php://input"), false);

                    // 資料驗證
                    $errors = $this->getValidationErrors($data);
                    
                    // 伺服器理解請求並且語法正確，但它無法處理。(沒有通過資料驗證)
                    if (!empty($errors)) {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    $rows = $this->gateway->updateForUser($this->user_id, $id, $data);
                    echo json_encode(["message" => "Task updated", "rows" => $rows]);
                    break;

                case "DELETE": 
                    $rows = $this->gateway->deleteForUser($this->user_id, $id);
                    echo json_encode(["message" => "Task deleted", "rows" => $rows]);
                    break;

                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    private function respondMethodNotAllowed(string $allowed_methods): void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Task with ID $id not found"]);
    }

    private function respondCreated(string $id): void
    {
        http_response_code(201);
        echo json_encode(["message" => "Task created", "id" => $id]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if ($is_new && empty($data["name"])) {
            $errors[] = "name is required";
        }

        if (!empty($data["priority"])) {
            if (filter_var($data["priority"], FILTER_VALIDATE_INT) === false) {
                $errors[] = "priority must be an integer";
            }
        }

        return $errors;
    }
}