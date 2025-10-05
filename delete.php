<?php
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if(!empty($data->id)) {
    
    if(file_exists('users.json')) {
        $users = json_decode(file_get_contents('users.json'), true);
        
        $initialCount = count($users);
        $users = array_filter($users, function($user) use ($data) {
            return isset($user['id']) && $user['id'] != $data->id;
        });
        
        if(count($users) < $initialCount) {
            file_put_contents('users.json', json_encode(array_values($users), JSON_PRETTY_PRINT));
            
            http_response_code(200);
            echo json_encode([
                "success" => true,
                "message" => "User deleted successfully."
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "success" => false,
                "message" => "User not found."
            ]);
        }
    } else {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "No users found."
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Unable to delete user. User ID is required."
    ]);
}
?>