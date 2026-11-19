<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can delete vehicles.");
}

require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Invalid request.");
}

if (!isset($_POST['vehicle_id']) || !is_numeric($_POST['vehicle_id'])) {
    die("Invalid vehicle ID.");
}

$vehicle_id = (int) $_POST['vehicle_id'];

$stmt = $conn->prepare(
    "DELETE FROM vehicles WHERE vehicle_id = ?"
);

$stmt->bind_param("i", $vehicle_id);

if ($stmt->execute()) {
    header("Location: view_vehicles.php");
    exit;
} else {
    echo "Unable to delete vehicle. It may be linked to another record.";
}

$stmt->close();
$conn->close();
?>