<?php
// CORS headers
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Get posted data
$input = file_get_contents("php://input");
$data = json_decode($input);

// Debug log
$debug_log = "=== UPDATE.PHP CALLED ===\n";
$debug_log .= "Time: " . date('Y-m-d H:i:s') . "\n";
$debug_log .= "Raw Input: " . $input . "\n";
$debug_log .= "Decoded Data: " . print_r($data, true) . "\n";

try {
    // Validate data
    if(empty($data->id)) {
        throw new Exception("User ID is required");
    }

    if(empty($data->name) && empty($data->email) && empty($data->phone)) {
        throw new Exception("At least one field (name, email, or phone) is required to update");
    }

    // Get existing users
    $users_file = 'users.json';
    if(!file_exists($users_file)) {
        throw new Exception("No users database found");
    }

    $users_data = file_get_contents($users_file);
    $users = json_decode($users_data, true);
    
    if($users === null) {
        $users = [];
    }

    $debug_log .= "Total users in database: " . count($users) . "\n";
    $debug_log .= "Looking for user ID: " . $data->id . "\n";

    $userFound = false;
    foreach($users as &$user) {
        if(isset($user['id']) && $user['id'] == $data->id) {
            $debug_log .= "Found user: " . print_r($user, true) . "\n";
            
            // Update fields if provided
            if(!empty($data->name)) {
                $user['name'] = $data->name;
            }
            if(!empty($data->email)) {
                $user['email'] = $data->email;
            }
            if(!empty($data->phone)) {
                $user['phone'] = $data->phone;
            }
            
            $user['updated_at'] = date('Y-m-d H:i:s');
            $userFound = true;
            $debug_log .= "User after update: " . print_r($user, true) . "\n";
            break;
        }
    }
    
    if($userFound) {
        // Save updated users
        $result = file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
        
        if($result === false) {
            throw new Exception("Failed to save updated users data");
        }
        
        $debug_log .= "Users data saved successfully\n";
        
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "User updated successfully.",
            "user_id" => $data->id
        ]);
    } else {
        $debug_log .= "User not found with ID: " . $data->id . "\n";
        throw new Exception("User not found with ID: " . $data->id);
    }
    
} catch (Exception $e) {
    $debug_log .= "ERROR: " . $e->getMessage() . "\n";
    
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

// Write debug log
file_put_contents('update_debug.log', $debug_log, FILE_APPEND | LOCK_EX);
?>