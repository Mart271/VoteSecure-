<<<<<<< HEAD
<?php
require_once __DIR__ . '/config/database.php';

$payload = json_encode([
    'model'    => QWEN_MODEL,
    'messages' => [
        ['role' => 'user', 'content' => 'Say hello in one sentence.'],
    ],
    'max_tokens' => 100,
]);

$ch = curl_init(QWEN_API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . QWEN_API_KEY,
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
$data = json_decode($response, true);
echo "Response: " . ($data['choices'][0]['message']['content'] ?? $response);
=======
<?php
require_once __DIR__ . '/config/database.php';

$payload = json_encode([
    'model'    => QWEN_MODEL,
    'messages' => [
        ['role' => 'user', 'content' => 'Say hello in one sentence.'],
    ],
    'max_tokens' => 100,
]);

$ch = curl_init(QWEN_API_URL);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . QWEN_API_KEY,
        'Content-Type: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "<br>";
$data = json_decode($response, true);
echo "Response: " . ($data['choices'][0]['message']['content'] ?? $response);
>>>>>>> 4b4892c0a36933c726154fb629a76e5be16d9c40
?>