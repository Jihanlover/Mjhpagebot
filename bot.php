<?php

$token = "7928352883:AAH8y9m0AQbOzh-h24NEhvYXpYCRpGEz99c";
$apiURL = "https://api.telegram.org/bot$token/";

$update = json_decode(file_get_contents("php://input"), TRUE);

$chatId = $update["message"]["chat"]["id"];
$message = $update["message"]["text"];

$step_file = "step_$chatId.txt";

// Step manager
if (!file_exists($step_file)) {
    file_put_contents($step_file, "start");
}
$step = file_get_contents($step_file);

if ($message == "/start") {
    sendMessage($chatId, "👋 Welcome! Send me the filename (without .html)");
    file_put_contents($step_file, "wait_filename");

} elseif ($step == "wait_filename") {
    file_put_contents("data/filename_$chatId.txt", $message);
    sendMessage($chatId, "✅ Got it!\nNow send your HTML code (ex: <h1>Hello</h1>)");
    file_put_contents($step_file, "wait_html");

} elseif ($step == "wait_html") {
    $filename = file_get_contents("data/filename_$chatId.txt");
    $htmlcode = urlencode($message);

    $api_call = "https://testgm.22web.org/api.php?filename=$filename&htmlcode=$htmlcode";
    $response = file_get_contents($api_call);

    if (strpos($response, 'success') !== false || strlen($response) > 5) {
        $link = "https://testgm.22web.org/{$filename}.html";
        sendMessage($chatId, "✅ Your webpage is ready:\n🔗 $link");
    } else {
        sendMessage($chatId, "❌ Failed to create page. Please try again.");
    }

    unlink($step_file);
    unlink("data/filename_$chatId.txt");
} else {
    sendMessage($chatId, "ℹ️ Please type /start to begin.");
}

// Send message function
function sendMessage($chatId, $message) {
    global $apiURL;
    file_get_contents($apiURL . "sendMessage?chat_id=$chatId&text=" . urlencode($message));
}
?>
