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

$input = file_get_contents(("php://input"));
$data = json_decode($input);

// Debug log
error_log("CREATE.PHP - Received: " . $input);

if (!empty($data->name) && !empty($data->email) && !empty($data->phone)) {

    $users = [];
    $users_file = 'users.json';

    if (file_exists($users_file)) {
        $users_data = file_get_contents($users_file);
        $users = json_decode($users_data, true) ?: [];
    }

    $newUser = [
        'id' => uniqid(),
        'name' => $data->name,
        'email' => $data->email,
        'phone' => $data->phone,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $users[] = $newUser;

    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User created successfully.",
        "user" => $newUser
    ]);

    error_log("CREATE.PHP - User created: " . json_encode($newUser));
} else {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Unable to create user. Data is incomplete."
    ]);
}
