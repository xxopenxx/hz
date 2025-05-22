<?php
define('IN_ENGINE', TRUE);
define('BASE_DIR', __DIR__);
define('SERVER_DIR', __DIR__ . '/server');

require_once(SERVER_DIR . '/src/Utils/functions.php');
require_once(SERVER_DIR . '/src/Utils/field.php');
require_once(SERVER_DIR . '/src/Utils/autoloader.php');

\Srv\Config::__init();
\Srv\DB::__init();

header('Content-Type: application/json; charset=utf-8');

// Initialize response array
$response = [];

$action = isset($_POST['action']) ? $_POST['action'] : '';
$session_id = isset($_POST['session_id']) ? $_POST['session_id'] : null;

function escapeHtml($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Enable error logging for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    if(!$session_id) {
        throw new Exception('Invalid session');
    }

    $user = \Srv\DB::$db->table('user')
        ->select('id')
        ->where('session_id', '=', $session_id)
        ->one();

    if(!$user) {
        throw new Exception('Invalid session');
    }

    $character = \Srv\DB::$db->table('character')
        ->select('id, user_id, name, level')
        ->where('user_id', '=', $user['id'])
        ->one();

    if(!$character) {
        throw new Exception('Character not found');
    }

    \Srv\DB::sql("CREATE TABLE IF NOT EXISTS `shoutbox_messages` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `message` text NOT NULL,
        `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    switch($action) {
        case 'get_character':
            $response['character'] = [
                'id' => $character['id'],
                'name' => escapeHtml($character['name']),
                'level' => (int)$character['level']
            ];
            break;
            
        case 'post':
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';

            if(empty($message)) {
                throw new Exception('Message cannot be empty');
            }

            if(strlen($message) > 200) {
                throw new Exception('Message is too long (max 200 characters)');
            }

            $escapedMessage = escapeHtml($message);
            
            $timestamp = date('Y-m-d H:i:s');
            
            // Insert with error checking
            try {
                $result = \Srv\DB::$db->table('shoutbox_messages')
                    ->insert([
                        'user_id' => $user['id'],
                        'message' => $escapedMessage,
                        'timestamp' => $timestamp
                    ])
                    ->execute();
                
                $messageId = \Srv\DB::lastInsertId();
                
                if(!$messageId) {
                    throw new Exception('Failed to insert message');
                }
                
                // Set the success response
                $response['success'] = true;
                $response['message'] = [
                    'id' => $messageId,
                    'user_id' => $user['id'],
                    'username' => escapeHtml($character['name']),
                    'level' => (int)$character['level'],
                    'message' => $escapedMessage,
                    'timestamp' => $timestamp,
                    'time' => date('H:i', strtotime($timestamp)),
                    'isSelf' => true
                ];
            } catch(Exception $e) {
                throw new Exception('Database error: ' . $e->getMessage());
            }
            break;

        case 'get':
            $last_id = (int)($_POST['last_id'] ?? 0);
            $limit = min((int)($_POST['limit'] ?? 50), 100);

            $rawQuery = "SELECT m.id, m.user_id, m.message, m.timestamp, c.name, c.level 
                        FROM shoutbox_messages m 
                        LEFT JOIN `character` c ON m.user_id = c.user_id";
            
            if($last_id > 0) {
                $rawQuery .= " WHERE m.id > " . $last_id . " ORDER BY m.id ASC";
            } else {
                $rawQuery .= " ORDER BY m.id DESC LIMIT " . $limit;
            }
            
            $messagesData = \Srv\DB::sql($rawQuery);
            $formatted_messages = [];

            foreach($messagesData as $msg) {
                if (!$msg['name']) continue;
                
                $formatted_messages[] = [
                    'id' => $msg['id'],
                    'user_id' => $msg['user_id'],
                    'username' => $msg['name'],
                    'level' => (int)$msg['level'],
                    'message' => $msg['message'],
                    'timestamp' => $msg['timestamp'],
                    'time' => date('H:i', strtotime($msg['timestamp'])),
                    'isSelf' => ($msg['user_id'] == $user['id'])
                ];
            }

            if($last_id === 0) {
                $formatted_messages = array_reverse($formatted_messages);
            }

            $response['messages'] = $formatted_messages;
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch(Exception $e) {
    $response = [
        'error' => $e->getMessage(),
        'success' => false
    ];
}

// Ensure the response is never empty
if (empty($response)) {
    $response = ['error' => 'Unknown error occurred', 'success' => false];
}

// Output the response
echo json_encode($response);
exit;