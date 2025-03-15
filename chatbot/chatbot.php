<?php
session_start();
include '../database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_message = trim($_POST['message']);
    $response = "I'm sorry, I don't understand that.";

    // AI Chatbot Predefined Responses
    $responses = [
        "hello" => "Hello! How can I assist you?",
        "how to book a ride" => "To book a ride, go to the 'Available Rides' page and click on the 'Book Seat' button.",
        "how to post a ride" => "To post a ride, navigate to 'Post a Ride' and enter details like source, destination, seats, and fare.",
        "how can i cancel my booking" => "You can cancel a pending booking from the 'My Bookings' page.",
        "how do i know my booking is confirmed" => "Your booking status will change to 'Approved' once the driver accepts it.",
        "payment methods" => "We accept UPI and cash payments.",
        "thank you" => "You're welcome! Let me know if you need more help."
    ];

    foreach ($responses as $key => $value) {
        if (stripos($user_message, $key) !== false) {
            $response = $value;
            break;
        }
    }

    echo json_encode(["response" => $response]);
    exit();
}
?>
