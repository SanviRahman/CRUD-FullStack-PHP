<?php
// CORS HEADERS 
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST"); // Only allow POST
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// REQUEST VALIDATION 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); 
    echo json_encode([
        "success" => false,
        "message" => "Only POST method is allowed."
    ]);
    exit();
}

//  READ JSON INPUT 
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Debug log
error_log("CREATE.PHP - Received: " . $input);

//  VALIDATE INPUT
if (!empty($data['name']) && !empty($data['email']) && !empty($data['phone'])) {

    $users = [];
    $users_file = 'users.json';

    // Read existing users
    if (file_exists($users_file)) {
        $users_data = file_get_contents($users_file);
        $users = json_decode($users_data, true) ?: [];
    }

    // Create new user
    $newUser = [
        'id' => uniqid(),
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'created_at' => date('Y-m-d H:i:s')
    ];

    $users[] = $newUser;

    // Save updated users with file lock
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

    // Success response
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "User created successfully.",
        "user" => $newUser
    ]);

    error_log("CREATE.PHP - User created: " . json_encode($newUser));

} else {
    // Invalid data
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Unable to create user. Data is incomplete."
    ]);
}
?>
