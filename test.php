<?php

// Sending the API request to get Bitcoin price in UAH in JSON
$apiBitcoin = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=uah';
$response = file_get_contents($apiBitcoin);
$data = json_decode($response, true);

// Check if the response contains Bitcoin data
if (isset($data['bitcoin']['uah'])) {
    $bitcoinPrice = $data['bitcoin']['uah'];
    echo 'Current Bitcoin Price: ' . $bitcoinPrice . ' UAH';
} else {
    echo 'Failed to retrieve Bitcoin price.';
}
?>
