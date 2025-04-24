<?php
include '../database/db_config.php';

$query = $_GET['query'];
$type = $_GET['type'];

// Ensure query and type are sanitized
$query = $conn->real_escape_string($query);

// Validate input type to prevent SQL injection and unexpected behavior
$type = ($type == 'pickup' || $type == 'drop') ? $type : 'pickup'; // Default to 'pickup' if invalid

// Define which column to search based on the location type
$column = ($type == 'pickup') ? 'pickup_location' : 'drop_location';

// Prepare the SQL query
$sql = "SELECT DISTINCT $column FROM rides WHERE $column LIKE ? LIMIT 5";
$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

// Prepare the response
$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row[$column];
}

$stmt->close();
$conn->close();

// Return the results as JSON
echo json_encode($locations);
?>
