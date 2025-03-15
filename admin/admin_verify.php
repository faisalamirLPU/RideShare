<?php
session_start();
require_once '../database/db_config.php';

// Check if user is an admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Approve or Reject Driver
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['driver_id'], $_POST['action'])) {
    $driver_id = intval($_POST['driver_id']);
    $action = ($_POST['action'] === 'approve') ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE drivers SET verified = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $driver_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch pending drivers
$stmt = $conn->prepare("SELECT d.id, u.name, u.email, d.id_proof, d.driving_license 
                        FROM drivers d 
                        JOIN users u ON d.user_id = u.id 
                        WHERE d.verified = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Drivers - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="admin-container">
        <h2>Pending Driver Verifications</h2>
        <?php while ($driver = $result->fetch_assoc()) { ?>
            <div class="driver-card">
                <p><strong>Name:</strong> <?= htmlspecialchars($driver['name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($driver['email']) ?></p>
                <p><strong>ID Proof:</strong> <a href="<?= htmlspecialchars($driver['id_proof']) ?>" target="_blank">View</a></p>
                <p><strong>Driving License:</strong> <a href="<?= htmlspecialchars($driver['driving_license']) ?>" target="_blank">View</a></p>
                <form method="POST">
                    <input type="hidden" name="driver_id" value="<?= htmlspecialchars($driver['id']) ?>">
                    <button type="submit" name="action" value="approve">Approve</button>
                    <button type="submit" name="action" value="reject">Reject</button>
                </form>
            </div>
        <?php } ?>
    </div>
</body>
</html>
