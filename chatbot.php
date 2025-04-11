<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = strtolower(trim($_POST['message'] ?? ''));

    $responses = [
        "hi" => "Hello! How can I help you with your ride?",
        "hello" => "Hey there! Need to book or offer a ride?",
        "book ride" => "You can book a ride by filling the form above!",
        "offer ride" => "Click on 'Offer Ride' in the menu to post your ride.",
        "bye" => "Goodbye! Safe travels 🚗💨",
        "thanks" => "You're welcome! 😊",
    ];

    $reply = "I'm not sure how to respond to that. Try asking about booking or offering a ride.";

    foreach ($responses as $keyword => $response) {
        if (strpos($userMessage, $keyword) !== false) {
            $reply = $response;
            break;
        }
    }

    echo $reply;
}
?>
