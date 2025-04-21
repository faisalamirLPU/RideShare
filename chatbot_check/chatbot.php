<?php
// chatbot/chatbot.php
header('Content-Type: application/json');

include '../database/db_config.php'; // Your DB connection
require_once '../vendor/autoload.php'; // If you're using Composer

use Orhanerday\OpenAi\OpenAi;

$open_ai_key = 'YOUR_OPENAI_API_KEY'; // Replace with your key
$open_ai = new OpenAi($open_ai_key);

// Step 1: Receive User Message
$data = json_decode(file_get_contents("php://input"), true);
$userMessage = trim($data['message']);

if (!$userMessage) {
    echo json_encode(["response" => "Please ask something."]);
    exit;
}

// Step 2: Generate Embedding for User Input
$userEmbedding = null;

$response = $open_ai->embeddings([
    "model" => "text-embedding-ada-002",
    "input" => $userMessage,
]);

$responseData = json_decode($response, true);
if (isset($responseData['data'][0]['embedding'])) {
    $userEmbedding = $responseData['data'][0]['embedding'];
} else {
    echo json_encode(["response" => "Error getting embedding."]);
    exit;
}

// Step 3: Compare with MySQL stored embeddings using cosine similarity
function cosineSimilarity($vec1, $vec2) {
    $dotProduct = 0;
    $normA = 0;
    $normB = 0;
    for ($i = 0; $i < count($vec1); $i++) {
        $dotProduct += $vec1[$i] * $vec2[$i];
        $normA += pow($vec1[$i], 2);
        $normB += pow($vec2[$i], 2);
    }
    return $normA && $normB ? $dotProduct / (sqrt($normA) * sqrt($normB)) : 0;
}

// Step 4: Fetch all stored embeddings and find the best match
$bestMatch = null;
$highestScore = -1;

$result = $conn->query("SELECT content, embedding FROM embeddings");
while ($row = $result->fetch_assoc()) {
    $storedEmbedding = json_decode($row['embedding'], true);
    $similarity = cosineSimilarity($userEmbedding, $storedEmbedding);
    if ($similarity > $highestScore) {
        $highestScore = $similarity;
        $bestMatch = $row['content'];
    }
}

if ($bestMatch && $highestScore > 0.75) {
    // Optional: You can send this to GPT to generate a nice answer
    $prompt = "User asked: \"$userMessage\"\n\nRelevant Info:\n$bestMatch\n\nRespond appropriately:";
    $gptResponse = $open_ai->chat([
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "You are a helpful assistant for RideShare users."],
            ["role" => "user", "content" => $prompt]
        ],
    ]);
    $output = json_decode($gptResponse, true);
    echo json_encode(["response" => $output['choices'][0]['message']['content']]);
} else {
    echo json_encode(["response" => "Sorry, I couldn’t find an answer based on your question."]);
}
?>
