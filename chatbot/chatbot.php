<?php
session_start();
include '../database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_message = trim(strtolower($_POST['message']));
    $response = "I'm sorry, I don't understand that.";
    $options = [];

    // AI Chatbot Predefined Responses with optional follow-ups
    $responses = [
        "hello" => [
            "text" => "Hello! How can I assist you today?",
            "options" => [
                "How to book a ride",
                "How to post a ride",
                "Payment methods"
            ]
        ],
        "hi" => [
            "text" => "Hi there! Need any help?",
            "options" => [
                "How to book a ride",
                "How can I cancel my booking",
                "Payment methods"
            ]
        ],
        "how to book a ride" => [
            "text" => "To book a ride, go to the 'Available Rides' page and click on the 'Book Seat' button."
        ],
        "how to post a ride" => [
            "text" => "To post a ride, navigate to 'Post a Ride' and enter details like source, destination, seats, and fare."
        ],
        "how can i cancel my booking" => [
            "text" => "You can cancel a pending booking from the 'My Bookings' page."
        ],
        "how do i know my booking is confirmed" => [
            "text" => "Your booking status will change to 'Approved' once the driver accepts it."
        ],
        "payment methods" => [
            "text" => "We accept UPI and cash payments."
        ],
        "thank you" => [
            "text" => "You're welcome! Let me know if you need more help."
        ]
    ];

    foreach ($responses as $key => $data) {
        if (stripos($user_message, $key) !== false) {
            $response = $data["text"];
            if (isset($data["options"])) {
                $options = $data["options"];
            }
            break;
        }
    }

    echo json_encode([
        "response" => $response,
        "options" => $options
    ]);
    exit();
}
?>
