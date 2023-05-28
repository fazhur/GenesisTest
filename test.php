<?php
$bitcoinApiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=uah';
$sendgridApiUrl = 'https://api.sendgrid.com/v3/mail/send';
$sendgridApiKey = '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';
$filePath = 'emails.txt';

function getBitcoinPrice()
{
    global $bitcoinApiUrl;
    $bitcoinResponse = file_get_contents($bitcoinApiUrl);
    $bitcoinData = json_decode($bitcoinResponse, true);
    $bitcoinPrice = isset($bitcoinData['bitcoin']['uah']) ? $bitcoinData['bitcoin']['uah'] : 'N/A';
    return $bitcoinPrice;
}

function sendPrice($email) {
    global $bitcoinApiUrl;
    global $sendgridApiUrl;
    global $sendgridApiKey;

    $emailContent = 'The current price of Bitcoin is ' . getBitcoinPrice() . ' UAH.';

    // Create JSON data for sending the email
    $emailData = [
        'personalizations' => [
            [
                'to' => [
                    ['email' => $email]
                ],
                'subject' => 'Bitcoin Price Alert'
            ]
        ],
        'from' => [
            'email' => 'fedir.zhurba@ucu.edu.ua'
        ],
        'content' => [
            [
                'type' => 'text/plain',
                'value' => $emailContent
            ]
        ]
    ];
    $ch = curl_init($sendgridApiUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $sendgridApiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
}

function subscribeEmail($email) {
    global $filePath;

    $fileLines = file($filePath);
    $emailFound = false;

    foreach ($fileLines as $line) {
        $line = trim($line);
        if ($line === $email) {
            $emailFound = true;
            break;
        }
    }
    if ($emailFound) {
        return false;
    }
    else {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            file_put_contents($filePath, $email . PHP_EOL, FILE_APPEND);
        }
        return true;
    }
}

function sendToAll($filePath) {
    $fileLines = file($filePath);
    foreach ($fileLines as $line) {
        sendPrice(trim($line));
    }
} 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ans  = getBitcoinPrice();
    if ($ans == "N/A"){
        http_response_code(400);
    }
    else{
        echo $ans;
        http_response_code(200);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SERVER['REQUEST_URI'] === '/api/subscribe') {
        $email = $_POST['email'];
        $ans = subscribeEmail($email);
        if ($ans) {
            http_response_code(200);
        }
        else {
            http_response_code(400);
        }
    }
    if ($_SERVER['REQUEST_URI'] === '/api/sendEmails') {
        sendToAll($filePath);
        http_response_code(200);
    }
}
?>
