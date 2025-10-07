<?php
header("Access-Control-Allow-Origin: *"); 
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Method Check 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["message" => "Only GET method is allowed."]);
    exit();
}

//  JSON File Path
$users_file = 'users.json';

// Read Users 
if (file_exists($users_file)) {
    $users_data = file_get_contents($users_file);
    $users = json_decode($users_data, true);

    if (!is_array($users)) {
        $users = [];
    }

 
    http_response_code(200);
    echo json_encode($users, JSON_PRETTY_PRINT);
} else {
    http_response_code(200);
    echo json_encode([], JSON_PRETTY_PRINT);
}
?>
