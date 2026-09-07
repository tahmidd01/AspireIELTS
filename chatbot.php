<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the user's message from the frontend
    $user_message = $_POST['message'];

    // Your OpenAI API secret key (make sure it's securely stored)
    $api_key = $_ENV['OPENAI_API_KEY'] ?? '';
    // Set up the URL for OpenAI API
    $url = 'https://api.openai.com/v1/chat/completions';

    // Prepare the headers
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ];

    // Prepare the data to send to the API
    $data = [
        'model' => 'gpt-4',  // Use the GPT-4 model
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant for the AspireIELTS platform.'],
            ['role' => 'user', 'content' => $user_message],
        ],
        'temperature' => 0.7,
    ];

    // Setup the cURL request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the request and get the response
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response) {
        $response_data = json_decode($response, true);
        echo json_encode(['reply' => $response_data['choices'][0]['message']['content']]);
    } else {
        echo json_encode(['reply' => 'Error: Unable to connect to the AI service.']);
    }
}
?>
