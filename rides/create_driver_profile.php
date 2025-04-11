<?php
session_start();
include '../database/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $vehicle_number = trim($_POST['vehicle_number']);
    $vehicle_model = trim($_POST['vehicle_model']);
    $id_proof = $_FILES['id_proof']['name'];
    $license = $_FILES['driving_license']['name'];
    $vehicle_image = $_FILES['vehicle_image']['name'];

    $upload_dir = "../uploads/";
    move_uploaded_file($_FILES['id_proof']['tmp_name'], $upload_dir . $id_proof);
    move_uploaded_file($_FILES['driving_license']['tmp_name'], $upload_dir . $license);
    move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $upload_dir . $vehicle_image);

    $stmt = $conn->prepare("INSERT INTO drivers (user_id, id_proof, driving_license, vehicle_number, vehicle_model, vehicle_image, verified, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("isssss", $user_id, $id_proof, $license, $vehicle_number, $vehicle_model, $vehicle_image);

    if ($stmt->execute()) {
        $message = "Driver profile created successfully. Awaiting verification.";
    } else {
        $message = "Error creating driver profile: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Driver Profile</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="form-container">
    <h2>Create Driver Profile</h2>
    <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Vehicle Number:</label>
        <input type="text" name="vehicle_number" required>

        <label>Vehicle Model:</label>
        <input type="text" name="vehicle_model" required>

        <label>ID Proof (PDF or Image):</label>
        <input type="file" name="id_proof" accept=".pdf,.jpg,.jpeg,.png" required>

        <label>Driving License (PDF or Image):</label>
        <input type="file" name="driving_license" accept=".pdf,.jpg,.jpeg,.png" required>

        <label>Vehicle Image:</label>
        <input type="file" name="vehicle_image" accept=".jpg,.jpeg,.png" required>

        <button type="submit">Submit Profile</button>
    </form>
</div>
</body>
</html>
