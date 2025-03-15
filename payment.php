<?php
session_start();
require_once 'database/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if (!isset($_GET['booking_id'])) {
    die("Invalid access.");
}

$booking_id = intval($_GET['booking_id']);
$user_id = $_SESSION['user_id'];

// Fetch booking details
$sql = "SELECT b.*, r.fare, r.driver_id FROM bookings b
        JOIN rides r ON b.ride_id = r.id 
        WHERE b.id = ? AND b.passenger_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    die("Booking not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $transaction_id = $payment_method === 'UPI' ? $_POST['transaction_id'] : null;
    $amount = $booking['fare'] * $booking['seats_booked'];

    // Insert payment record
    $sql = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, payment_status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $booking_id, $amount, $payment_method, $transaction_id);
    
    if ($stmt->execute()) {
        // Update booking payment status
        $update_booking = $conn->prepare("UPDATE bookings SET payment_status = 'paid' WHERE id = ?");
        $update_booking->bind_param("i", $booking_id);
        $update_booking->execute();

        echo "<script>alert('Payment successful!'); window.location.href='users/my_bookings.php';</script>";
    } else {
        echo "<script>alert('Payment failed! Try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <?php include 'includes/user_header.php'; ?>

    <div class="payment-container">
        <h2>Make Payment</h2>
        <p>Ride Fare: ₹<?php echo number_format($booking['fare'] * $booking['seats_booked'], 2); ?></p>

        <form method="POST">
            <label>Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="UPI">UPI</option>
                <option value="cash">Cash (Pay to Driver)</option>
            </select>

            <div id="upi_section" style="display: none;">
                <label>UPI Transaction ID:</label>
                <input type="text" name="transaction_id" placeholder="Enter UPI Transaction ID">
            </div>

            <button type="submit">Confirm Payment</button>
        </form>
    </div>

    <script>
        document.getElementById('payment_method').addEventListener('change', function() {
            document.getElementById('upi_section').style.display = this.value === 'UPI' ? 'block' : 'none';
        });
    </script>

    <?php include 'includes/user_footer.php'; ?>
</body>
</html>
