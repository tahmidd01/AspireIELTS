<?php
function callOpenRouterAPI($apiKey, $messages, $model = "deepseek/deepseek-chat:free") {
    $url = "https://openrouter.ai/api/v1/chat/completions";

    $payload = json_encode([
        "model" => $model,
        "messages" => $messages
    ]);

    $headers = [
        "Authorization: Bearer $apiKey",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$status, $response];
}

function openRouterRequestWithRetry($apiKey, $messages, $maxRetries = 3) {
    $retryDelay = 2; // seconds
    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        list($status, $response) = callOpenRouterAPI($apiKey, $messages);

        if ($status === 200) {
            $json = json_decode($response, true);
            if ($json !== null) {
                return $json; // success
            }
            // invalid JSON, maybe retry
        } elseif ($status >= 500) {
            // server error, retry
        } else {
            // client error or auth error, no retry
            throw new Exception("OpenRouter API Error: HTTP $status - $response");
        }

        // Wait before retrying
        sleep($retryDelay);
        $retryDelay *= 2; // exponential backoff
    }

    throw new Exception("OpenRouter API failed after $maxRetries retries.");
}

// --- USAGE EXAMPLE ---

$apiKey = $_ENV['DEEPSEEK_API_KEY'] ?? '';

$messages = [
    [
        "role" => "system",
        "content" => "You are an IELTS examiner. Evaluate the writing based on Task Response, Coherence and Cohesion, Lexical Resource, and Grammatical Range and Accuracy. Return ONLY this exact JSON format WITHOUT any additional text or explanation: {\"score\": number, \"feedback\": string}"
    ],
    [
        "role" => "user",
        "content" => "Prompt:\nExplain the impact of technology on education.\n\nUser Response:\nTechnology has revolutionized education by enabling remote learning."
    ]
];

try {
    $result = openRouterRequestWithRetry($apiKey, $messages);
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
