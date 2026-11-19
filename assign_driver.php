<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can assign drivers.");
}

require_once 'db_connect.php';

$message = "";

// Process driver assignment
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $vehicle_id = (int) $_POST['vehicle_id'];
    $driver_id = (int) $_POST['driver_id'];

    if ($vehicle_id <= 0 || $driver_id <= 0) {
        $message = "Please select both a vehicle and a driver.";

    } else {
        $stmt = $conn->prepare(
            "UPDATE vehicles SET driver_id = ? WHERE vehicle_id = ?"
        );

        $stmt->bind_param("ii", $driver_id, $vehicle_id);

        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $message = "Driver assigned successfully!";
        } else {
            $message = "Assignment failed: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Get vehicles
$vehicles = $conn->query(
    "SELECT vehicle_id, registration_number
     FROM vehicles
     ORDER BY registration_number"
);

// Get drivers
$drivers = $conn->query(
    "SELECT driver_id, license_number, user_id
     FROM drivers
     WHERE status = 'active'
     ORDER BY driver_id"
);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Driver</title>
</head>
<body>

<h2>Assign Driver to Vehicle</h2>

<p><?php echo htmlspecialchars($message); ?></p>

<form method="POST">

    <label>Select Vehicle:</label><br>

    <select name="vehicle_id" required>
        <option value="">-- Select Vehicle --</option>

        <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
            <option value="<?php echo $vehicle['vehicle_id']; ?>">
                <?php echo htmlspecialchars($vehicle['registration_number']); ?>
            </option>
        <?php endwhile; ?>

    </select>

    <br><br>

    <label>Select Driver:</label><br>

    <select name="driver_id" required>
        <option value="">-- Select Driver --</option>

        <?php while ($driver = $drivers->fetch_assoc()): ?>
            <option value="<?php echo $driver['driver_id']; ?>">
                <?php
                echo "Driver ID: " . $driver['driver_id'] .
                     " - Licence: " . htmlspecialchars($driver['license_number']);
                ?>
            </option>
        <?php endwhile; ?>

    </select>

    <br><br>

    <button type="submit">Assign Driver</button>

</form>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>