<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only PUT method is allowed."]);
    exit();
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

$debug_log = "=== UPDATE.PHP CALLED ===\n";
$debug_log .= "Time: " . date('Y-m-d H:i:s') . "\n";
$debug_log .= "Raw Input: " . $input . "\n";
$debug_log .= "Decoded Data: " . print_r($data, true) . "\n";

try {
    if (empty($data['id'])) throw new Exception("User ID is required");
    if (empty($data['name']) && empty($data['email']) && empty($data['phone']))
        throw new Exception("At least one field (name, email, or phone) is required to update");

    $users_file = 'users.json';
    if (!file_exists($users_file)) throw new Exception("No users database found");

    $users = json_decode(file_get_contents($users_file), true) ?: [];

    $debug_log .= "Total users in database: " . count($users) . "\n";
    $debug_log .= "Looking for user ID: " . $data['id'] . "\n";

    $userFound = false;
    foreach ($users as &$user) {
        if (isset($user['id']) && $user['id'] == $data['id']) {
            if (!empty($data['name'])) $user['name'] = $data['name'];
            if (!empty($data['email'])) $user['email'] = $data['email'];
            if (!empty($data['phone'])) $user['phone'] = $data['phone'];
            $user['updated_at'] = date('Y-m-d H:i:s');
            $userFound = true;
            break;
        }
    }

    if ($userFound) {
        if (file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX) === false)
            throw new Exception("Failed to save updated users data");

        http_response_code(200);
        echo json_encode(["success" => true, "message" => "User updated successfully.", "user_id" => $data['id']]);
    } else {
        throw new Exception("User not found with ID: " . $data['id']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    $debug_log .= "ERROR: " . $e->getMessage() . "\n";
}

file_put_contents('update_debug.log', $debug_log, FILE_APPEND | LOCK_EX);
?>
