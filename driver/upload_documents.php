<?php
session_start();
include '../database/db_config.php';

// Check if user is logged in as driver
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header("Location: ../auth/login.php");
    exit();
}

$driver_id = $_SESSION['user_id'];
$message = "";

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "../driver_uploads/";
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];

    // ID Proof Upload
    $id_proof = $_FILES['id_proof']['name'];
    $id_proof_tmp = $_FILES['id_proof']['tmp_name'];
    $id_proof_ext = strtolower(pathinfo($id_proof, PATHINFO_EXTENSION));

    // Driving License Upload
    $license = $_FILES['driving_license']['name'];
    $license_tmp = $_FILES['driving_license']['tmp_name'];
    $license_ext = strtolower(pathinfo($license, PATHINFO_EXTENSION));

    if (!in_array($id_proof_ext, $allowed_types) || !in_array($license_ext, $allowed_types)) {
        $message = "Only JPG, PNG, and PDF files are allowed.";
    } else {
        $id_proof_path = $target_dir . "id_" . time() . "." . $id_proof_ext;
        $license_path = $target_dir . "license_" . time() . "." . $license_ext;

        if (move_uploaded_file($id_proof_tmp, $id_proof_path) && move_uploaded_file($license_tmp, $license_path)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO drivers (user_id, id_proof, driving_license, verified) VALUES (?, ?, ?, 'pending')");
            $stmt->bind_param("iss", $driver_id, $id_proof_path, $license_path);
            if ($stmt->execute()) {
                $message = "Documents uploaded successfully! Awaiting admin approval.";
            } else {
                $message = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error uploading files.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents - Rideshare</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="upload-container">
        <h2>Upload Verification Documents</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>
        <form method="POST" enctype="multipart/form-data">
            <label>ID Proof (JPG, PNG, PDF):</label>
            <input type="file" name="id_proof" required>
            
            <label>Driving License (JPG, PNG, PDF):</label>
            <input type="file" name="driving_license" required>

            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>
