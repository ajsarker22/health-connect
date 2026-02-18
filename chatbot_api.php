<?php
// This script uses the Groq API and reads the model from an environment variable.
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

// --- IMPORTANT: PASTE YOUR GROQ API KEY HERE ---
define('GROQ_API_KEY', getenv('GROQ_API_KEY'));

// --- Get the model from the environment variable, with a fallback ---
 $model = getenv('GROQ_MODEL');
if ($model === false) {
    // Fallback to a default model if the environment variable is not set
    $model = 'llama-3.1-8b-instant'; 
}

 $userMessage = trim($_POST['message']);
if (empty($userMessage)) {
    echo json_encode(['response' => "Hello! I'm your AI health assistant. How can I help you today?"]);
    exit;
}

// --- The AI's System Prompt ---
 $system_prompt = "You are a helpful and cautious AI assistant for a healthcare web application called 'Health Connect'. Your role is to provide general, non-personal health information and guide users on how to use the website. You must NEVER give a diagnosis or prescribe specific medication. If the user asks for a diagnosis, prescription, or anything beyond general information, you must refuse and tell them to consult a doctor. Keep your answers concise and easy to understand. Do not mention you are an AI model like GPT.";

// --- Prepare the data payload for the Groq API ---
 $data = [
    'model' => $model, // Use the model from the environment variable
    'messages' => [
        ['role' => 'system', 'content' => $system_prompt],
        ['role' => 'user', 'content' => $userMessage]
    ],
    'max_tokens' => 250,
    'temperature' => 0.5,
];

// --- Use cURL to send the request to the Groq API ---
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . GROQ_API_KEY
]);

 $response = curl_exec($ch);
 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- Handle the API Response ---
if ($httpcode !== 200) {
    $error_data = json_decode($response, true);
    $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown API error.';
    echo json_encode(['response' => "I'm having trouble connecting to my brain right now. Please try again later. (Error: " . substr($error_message, 0, 50) . "... )"]);
} else {
    $result = json_decode($response, true);
    $ai_response = $result['choices'][0]['message']['content'];
    echo json_encode(['response' => trim($ai_response)]);
}