<?php
header('Content-Type: application/json');

// --- SECURITY: Only allow POST requests ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

define('GOOGLE_AI_API_KEY', 'AIzaSyCYGdJ9Ha7yOMIJlSvV5PiPdBMurSpeuwA');

 $userMessage = trim($_POST['message']);
if (empty($userMessage)) {
    echo json_encode(['response' => "Hello! I'm your AI health assistant. How can I help you today?"]);
    exit;
}

// --- The AI's System Prompt (Its Personality & Rules) ---
  $system_prompt = "You are a helpful and cautious AI assistant for a healthcare web application called 'Health Connect'.
Your role is to provide general, non-personal health information and guide users on how to use the website.
You must NEVER give a diagnosis or prescribe specific medication.
If the user asks for a diagnosis, prescription, or anything beyond general information, you must refuse and tell them to consult a doctor.
Keep your answers concise and easy to understand. Do not mention you are an AI model like GPT.";

// --- Prepare the data payload for the Google Gemini API ---
 $data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $system_prompt . "\n\nUser: " . $userMessage]
            ]
        ]
    ],
    'generationConfig' => [
        'maxOutputTokens' => 1000,
        'temperature' => 0.5,
    ]
];

// --- Use cURL to send the request to the Google Gemini API ---
 $ch = curl_init();
// *** USING THE CORRECT MODEL NAME FROM YOUR LIST ***
 $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=' . GOOGLE_AI_API_KEY;
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

 $response = curl_exec($ch);
 $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// --- Handle the API Response ---
if ($httpcode !== 200) {
    // If the API call failed for any reason
    $error_data = json_decode($response, true);
    $error_message = isset($error_data['error']['message']) ? $error_data['error']['message'] : 'Unknown API error.';
    echo json_encode(['response' => "I'm having trouble connecting to my brain right now. Please try again later. (Error: " . substr($error_message, 0, 50) . "... )"]);
} else {
    // If the API call succeeded
    $result = json_decode($response, true);
    $ai_response = $result['candidates'][0]['content']['parts'][0]['text'];
    echo json_encode(['response' => trim($ai_response)]);
}