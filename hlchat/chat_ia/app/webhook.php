<?php
require_once 'chat.php';
// Get the raw POST data
$rawData = file_get_contents("php://input");

// Decode JSON data to associative array
$data = json_decode($rawData, true);

// For debugging purposes, log the entire payload

// Extracting required data
$instance = $data['instance'] ?? null;
$remoteJid = $data['data']['key']['remoteJid'] ?? null;
$fromMe = $data['data']['key']['fromMe'] ?? null;
$conversation = $data['data']['message']['conversation'] ?? null;
$server_url = $data['server_url'] ?? null;
$apikey = $data['apikey'] ?? null;

// Example: Output the extracted data (or use it in your application)
file_put_contents('webhook_log.txt', $conversation,  FILE_APPEND);
$chat = new chat_ia();

if ($fromMe == false) {
    $chat->salvarConversa($remoteJid, $conversation, 'false', $instance, $server_url, $apikey);

}
?>
