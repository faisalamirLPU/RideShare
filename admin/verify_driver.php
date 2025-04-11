<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Handle approval or rejection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['driver_id'])) {
    $driver_id = intval($_POST['driver_id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    $update_query = "UPDATE drivers SET verified = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $action, $driver_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch pending drivers
$query = "SELECT d.*, u.name AS full_name, u.email 
          FROM drivers d 
          JOIN users u ON d.user_id = u.id 
          WHERE d.verified = 'pending'";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Drivers</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include 'include/header.php'; ?>

<div class="p-8">
    <h2 class="text-2xl font-bold mb-4">Pending Driver Applications</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="w-full border-collapse bg-white shadow-md rounded-lg">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="p-3 border">Name</th>
                    <th class="p-3 border">Email</th>
                    <th class="p-3 border">Vehicle</th>
                    <th class="p-3 border">Proofs</th>
                    <th class="p-3 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr class="hover:bg-gray-100">
                        <td class="p-3 border"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="p-3 border"><?= htmlspecialchars($row['email']) ?></td>
                        <td class="p-3 border"><?= htmlspecialchars($row['vehicle_model']) ?> (<?= htmlspecialchars($row['vehicle_number']) ?>)</td>
                        <td class="p-3 border">
                            <a href="../uploads/<?= $row['id_proof'] ?>" target="_blank">ID Proof</a> |
                            <a href="../uploads/<?= $row['driving_license'] ?>" target="_blank">License</a> |
                            <a href="../uploads/<?= $row['vehicle_image'] ?>" target="_blank">Vehicle Image</a>
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
        <p>No pending driver applications at the moment.</p>
    <?php endif; ?>
</div>

<?php include 'include/footer.php'; ?>
</body>
</html>
