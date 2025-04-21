<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['driver_id'], $_POST['action'])) {
    $driver_id = intval($_POST['driver_id']);
    $action_raw = $_POST['action'];

    if ($action_raw === 'approve') {
        $action = 'approved';
    } elseif ($action_raw === 'reject') {
        $action = 'rejected';
    } else {
        $action = '';
    }

    if (!empty($action)) {
        $update_query = "UPDATE drivers SET verified = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $action, $driver_id);
        if ($stmt->execute()) {
            $message = "Driver verification status updated successfully.";
        } else {
            $message = "Failed to update driver status.";
        }
        $stmt->close();
    } else {
        $message = "Invalid action.";
    }
}

// Fetch all drivers
$query = "SELECT d.*, u.name AS full_name, u.email 
          FROM drivers d 
          JOIN users u ON d.user_id = u.id 
          ORDER BY d.verified = 'pending' DESC, d.id DESC";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Verification</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include 'include/header.php'; ?>

<div class="p-8">
    <h2 class="text-2xl font-bold mb-4">Driver Applications</h2>

    <?php if (!empty($message)): ?>
        <div class="mb-4 p-3 rounded bg-blue-100 text-blue-700 border border-blue-300">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows > 0): ?>
        <table class="w-full border-collapse bg-white shadow-md rounded-lg">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-3 border">Name</th>
                    <th class="p-3 border">Email</th>
                    <th class="p-3 border">Vehicle</th>
                    <th class="p-3 border">Proofs</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Change Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="p-3 border"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="p-3 border"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="p-3 border"><?= htmlspecialchars($row['vehicle_model']) ?> (<?= htmlspecialchars($row['vehicle_number']) ?>)</td>
                        <td class="p-3 border">
                            <a href="../uploads/<?= urlencode($row['id_proof']) ?>" target="_blank">ID Proof</a> |
                            <a href="../uploads/<?= urlencode($row['driving_license']) ?>" target="_blank">License</a> |
                            <a href="../uploads/<?= urlencode($row['vehicle_image']) ?>" target="_blank">Vehicle Image</a>
                        </td>
                        <td class="p-3 border">
                            <?php if ($row['verified'] === 'approved'): ?>
                                <span class="text-green-600 font-semibold">Approved</span>
                            <?php elseif ($row['verified'] === 'rejected'): ?>
                                <span class="text-red-600 font-semibold">Rejected</span>
                            <?php else: ?>
                                <span class="text-yellow-600 font-semibold">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-3 border">
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="driver_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="approve" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded">Approve</button>
                                <button type="submit" name="action" value="reject" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded ml-2">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No driver applications found.</p>
    <?php endif; ?>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>
