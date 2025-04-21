<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Check if driver profile exists
$stmt = $conn->prepare("SELECT * FROM drivers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && $driver && $driver['verified'] === 'pending') {
    $vehicle_number = trim($_POST['vehicle_number']);
    $vehicle_model = trim($_POST['vehicle_model']);

    // Check if new files uploaded, else retain existing
    $upload_dir = "../uploads/";
    $id_proof = $_FILES['id_proof']['name'] ? $_FILES['id_proof']['name'] : $driver['id_proof'];
    $license = $_FILES['driving_license']['name'] ? $_FILES['driving_license']['name'] : $driver['driving_license'];
    $vehicle_image = $_FILES['vehicle_image']['name'] ? $_FILES['vehicle_image']['name'] : $driver['vehicle_image'];

    if ($_FILES['id_proof']['name']) move_uploaded_file($_FILES['id_proof']['tmp_name'], $upload_dir . $id_proof);
    if ($_FILES['driving_license']['name']) move_uploaded_file($_FILES['driving_license']['tmp_name'], $upload_dir . $license);
    if ($_FILES['vehicle_image']['name']) move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $upload_dir . $vehicle_image);

    $stmt = $conn->prepare("UPDATE drivers SET id_proof = ?, driving_license = ?, vehicle_number = ?, vehicle_model = ?, vehicle_image = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("sssssi", $id_proof, $license, $vehicle_number, $vehicle_model, $vehicle_image, $user_id);

    if ($stmt->execute()) {
        $message = "Profile updated successfully. Await admin verification.";
        header("Location: driver_profile_view.php"); // refresh to get updated data
        exit();
    } else {
        $message = "Update failed: " . $stmt->error;
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Driver Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">
    <?php include '../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto mt-10 bg-white p-8 rounded-xl shadow-lg">
        <h2 class="text-3xl font-bold mb-6">🚘 My Driver Profile</h2>

        <?php if ($message): ?>
            <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 rounded text-yellow-800">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($driver): ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <div>
                    <label class="block font-semibold">Vehicle Number</label>
                    <input type="text" name="vehicle_number" value="<?= htmlspecialchars($driver['vehicle_number']) ?>" class="w-full p-2 border rounded" <?= $driver['verified'] !== 'pending' ? 'readonly' : '' ?>>
                </div>

                <div>
                    <label class="block font-semibold">Vehicle Model</label>
                    <input type="text" name="vehicle_model" value="<?= htmlspecialchars($driver['vehicle_model']) ?>" class="w-full p-2 border rounded" <?= $driver['verified'] !== 'pending' ? 'readonly' : '' ?>>
                </div>

                <div>
                    <label class="block font-semibold">ID Proof</label><br>
                    <img src="../uploads/<?= $driver['id_proof'] ?>" alt="ID Proof" class="w-40 mb-2 rounded shadow">
                    <?php if ($driver['verified'] === 'pending'): ?>
                        <input type="file" name="id_proof">
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block font-semibold">Driving License</label><br>
                    <img src="../uploads/<?= $driver['driving_license'] ?>" alt="License" class="w-40 mb-2 rounded shadow">
                    <?php if ($driver['verified'] === 'pending'): ?>
                        <input type="file" name="driving_license">
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block font-semibold">Vehicle Image</label><br>
                    <img src="../uploads/<?= $driver['vehicle_image'] ?>" alt="Vehicle" class="w-40 mb-2 rounded shadow">
                    <?php if ($driver['verified'] === 'pending'): ?>
                        <input type="file" name="vehicle_image">
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <strong>Status:</strong>
                    <span class="inline-block px-3 py-1 text-sm rounded-full 
                        <?= $driver['verified'] === 'verified' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' ?>">
                        <?= ucfirst($driver['verified']) ?>
                    </span>
                </div>

                <?php if ($driver['verified'] === 'pending'): ?>
                    <button type="submit" class="mt-4 px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Update Profile
                    </button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="text-red-600">
                You haven't created a driver profile yet. <a href="/rideshare/create_driver_profile.php" class="underline text-blue-600">Create Now</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
