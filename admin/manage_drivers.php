<?php
session_start();
include('../database/db_config.php');
include('include/header.php');

// Verify request
if (isset($_GET['verify_id'])) {
  $id = $_GET['verify_id'];
  mysqli_query($conn, "UPDATE drivers SET verified=1 WHERE id=$id");
  header("Location: manage_drivers.php");
  exit();
}

$drivers = mysqli_query($conn, "SELECT d.*, u.name, u.email FROM drivers d JOIN users u ON d.user_id = u.id");
?>

<div class="p-10 text-white">
  <h2 class="text-2xl font-bold mb-6">Manage Drivers</h2>
  <table class="w-full table-auto bg-white/10 backdrop-blur rounded-xl border border-white/20">
    <thead class="bg-white/20 text-white font-semibold">
      <tr>
        <th class="p-3">Name</th>
        <th>Email</th>
        <th>Vehicle</th>
        <th>License</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody class="text-white text-center">
      <?php while($row = mysqli_fetch_assoc($drivers)) { ?>
        <tr class="border-t border-white/20">
          <td class="p-2"><?= $row['name'] ?></td>
          <td><?= $row['email'] ?></td>
          <td><?= $row['vehicle_model'] ?> (<?= $row['vehicle_number'] ?>)</td>
          <td><?= $row['driving_license'] ?></td>
          <td><?= $row['verified'] ? '✅ Verified' : '❌ Pending' ?></td>
          <td>
            <?php if (!$row['verified']) { ?>
              <a href="?verify_id=<?= $row['id'] ?>" class="text-green-400 hover:underline">Verify</a>
            <?php } else { ?>
              —
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<?php include('include/footer.php'); ?>
