<?php
// CORS Headers 
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Method Check 
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "success" => false,
        "message" => "Only DELETE method is allowed."
    ]);
    exit();
}

// Read JSON Input
$input = file_get_contents("php://input");
$data = json_decode($input, true); // associative array হিসেবে পড়া হচ্ছে

if (empty($data['id'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "User ID is required to delete."
    ]);
    exit();
}

$users_file = 'users.json';

if (!file_exists($users_file)) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "No users database found."
    ]);
    exit();
}

// Read existing users 
$users = json_decode(file_get_contents($users_file), true);
if (!is_array($users)) {
    $users = [];
}

$initialCount = count($users);

// Filter user list (remove matching ID) 
$users = array_filter($users, function($user) use ($data) {
    return isset($user['id']) && $user['id'] !== $data['id'];
});

if (count($users) < $initialCount) {
    file_put_contents($users_file, json_encode(array_values($users), JSON_PRETTY_PRINT), LOCK_EX);

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
?>
