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
        header("Location: driver_profile_view.php");
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
    <title>Driver Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen text-gray-800">

    <?php include '../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto mt-10 bg-white shadow-xl rounded-2xl p-8">
        <h2 class="text-3xl font-bold text-center mb-6">🚘 Driver Profile</h2>

        <?php if ($message): ?>
            <div class="mb-6 px-4 py-3 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 rounded">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($driver): ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold mb-1">Vehicle Number</label>
                    <input type="text" name="vehicle_number" value="<?= htmlspecialchars($driver['vehicle_number']) ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        <?= $driver['verified'] !== 'pending' ? 'readonly class="bg-gray-100 cursor-not-allowed"' : '' ?>>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Vehicle Model</label>
                    <input type="text" name="vehicle_model" value="<?= htmlspecialchars($driver['vehicle_model']) ?>"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        <?= $driver['verified'] !== 'pending' ? 'readonly class="bg-gray-100 cursor-not-allowed"' : '' ?>>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">ID Proof</label>
                    <img src="../uploads/<?= htmlspecialchars($driver['id_proof']) ?>" alt="ID Proof" class="w-40 h-auto mb-2 rounded shadow">
                    <?php if ($driver['verified'] === 'pending'): ?>
                        <input type="file" name="id_proof" class="block w-full text-sm text-gray-700 file:mr-4 file:py-1 file:px-3 file:border-0 file:rounded file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Driving License</label>
                    <img src="../uploads/<?= htmlspecialchars($driver['driving_license']) ?>" alt="Driving License" class="w-40 h-auto mb-2 rounded shadow">
                    <?php if ($driver['verified'] === 'pending'): ?>
                        <input type="file" name="driving_license" class="block w-full text-sm text-gray-700 file:mr-4 file:py-1 file:px-3 file:border-0 file:rounded file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Vehicle Image</label>
                    <img src="../uploads/<?= htmlspecialchars($driver['vehicle_image']) ?>" alt="Vehicle Image" class="w-40 h-auto mb-2 rounded shadow">
                    <?php if ($driver['verified'] === 'pending'): ?>
                        <input type="file" name="vehicle_image" class="block w-full text-sm text-gray-700 file:mr-4 file:py-1 file:px-3 file:border-0 file:rounded file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <?php endif; ?>
                </div>

                <div class="mt-4">
                    <label class="block font-semibold">Status:</label>
                    <span class="inline-block px-4 py-1 mt-1 text-sm font-medium rounded-full 
                        <?= $driver['verified'] === 'verified' ? 'bg-green-100 text-green-700' : ($driver['verified'] === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                        <?= ucfirst($driver['verified']) ?>
                    </span>
                </div>

                <?php if ($driver['verified'] === 'pending'): ?>
                    <button type="submit" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition duration-200">
                        Update Profile
                    </button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="text-center text-red-600 font-medium mt-6">
                You haven’t created a driver profile yet.<br>
                <a href="/rideshare/create_driver_profile.php" class="text-blue-600 underline mt-2 inline-block">Create Now</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
