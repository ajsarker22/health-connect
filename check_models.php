<?php
// This script will list all the models available to your Google AI account.
header('Content-Type: text/plain'); // Display as plain text for easy reading

// --- PASTE YOUR GOOGLE AI API KEY HERE ---
define('GOOGLE_AI_API_KEY', 'AIzaSyCYGdJ9Ha7yOMIJlSvV5PiPdBMurSpeuwA');

echo "Checking for available models for your API key...\n\n";

 $ch = curl_init();
// This is the endpoint to LIST models, not to generate content
 $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . GOOGLE_AI_API_KEY;

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

 $response = curl_exec($ch);
 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode === 200) {
    echo "SUCCESS! Here are your available models:\n";
    echo "-----------------------------------------\n";
    // The response is JSON, so we decode it and print it nicely
    $data = json_decode($response, true);
    foreach ($data['models'] as $model) {
        echo "Name: " . $model['name'] . "\n";
        echo "Display Name: " . $model['displayName'] . "\n";
        echo "Description: " . $model['description'] . "\n";
        echo "-----------------------------------------\n";
    }
} else {
    echo "ERROR: Failed to get model list.\n";
    echo "Status Code: " . $httpcode . "\n";
    echo "Response: " . $response . "\n";
}
?>