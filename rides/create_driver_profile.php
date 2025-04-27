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
    $profile_picture = '';

    $upload_dir = "../uploads/";

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = $_FILES['profile_picture']['name'];
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_dir . $profile_picture);
    }

    move_uploaded_file($_FILES['id_proof']['tmp_name'], $upload_dir . $id_proof);
    move_uploaded_file($_FILES['driving_license']['tmp_name'], $upload_dir . $license);
    move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $upload_dir . $vehicle_image);

    $stmt = $conn->prepare("INSERT INTO drivers (user_id, profile_picture, id_proof, driving_license, vehicle_number, vehicle_model, vehicle_image, verified, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("issssss", $user_id, $profile_picture, $id_proof, $license, $vehicle_number, $vehicle_model, $vehicle_image);

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
    <style>
        #camera-preview {
            width: 100%;
            max-width: 300px;
            margin: 10px 0;
            border: 2px dashed #aaa;
        }
    </style>
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

        <label>Profile Picture (Upload or Take a Selfie):</label><br>

        <!-- File Upload -->
        <input type="file" name="profile_picture" id="profile_picture_file" accept="image/*"><br><br>

        <!-- OR Take a Selfie -->
        <video id="camera-preview" autoplay></video><br>
        <button type="button" onclick="captureSelfie()">Capture Selfie</button><br><br>

        <!-- Hidden Canvas to capture photo -->
        <canvas id="selfie-canvas" style="display:none;"></canvas>

        <button type="submit">Submit Profile</button>
    </form>
</div>

<script>
    // Access the camera
    const video = document.getElementById('camera-preview');
    if (navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true })
        .then(function (stream) {
            video.srcObject = stream;
        })
        .catch(function (error) {
            console.log("Camera access denied or error: ", error);
        });
    }

    function captureSelfie() {
        const canvas = document.getElementById('selfie-canvas');
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert the canvas image to a Blob and update the file input
        canvas.toBlob(function(blob) {
            const file = new File([blob], "selfie.jpg", { type: "image/jpeg" });

            // Create a DataTransfer to simulate file upload
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            document.getElementById('profile_picture_file').files = dataTransfer.files;
            alert("Selfie captured! Ready for upload.");
        }, "image/jpeg");
    }
</script>

</body>
</html>
