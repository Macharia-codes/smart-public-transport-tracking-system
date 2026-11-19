<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Administrators only.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vehicle_id = (int) $_POST['vehicle_id'];
    $latitude = (float) $_POST['latitude'];
    $longitude = (float) $_POST['longitude'];

    if ($latitude < -90 || $latitude > 90 ||
        $longitude < -180 || $longitude > 180) {
        $message = "Invalid latitude or longitude.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO gps_locations (vehicle_id, latitude, longitude)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("idd", $vehicle_id, $latitude, $longitude);

        if ($stmt->execute()) {
            $message = "GPS location recorded successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$vehicles = $conn->query(
    "SELECT vehicle_id, registration_number FROM vehicles ORDER BY registration_number"
);

$locations = $conn->query(
    "SELECT g.latitude, g.longitude, g.recorded_at,
            v.registration_number
     FROM gps_locations g
     JOIN vehicles v ON g.vehicle_id = v.vehicle_id
     ORDER BY g.recorded_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>GPS Location Management</title>
</head>
<body>

<h2>Record Vehicle GPS Location</h2>

<?php if ($message !== ""): ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<form method="POST">

    <label>Select Vehicle:</label><br>
    <select name="vehicle_id" required>
        <option value="">Choose Vehicle</option>

        <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
            <option value="<?php echo $vehicle['vehicle_id']; ?>">
                <?php echo htmlspecialchars($vehicle['registration_number']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>

    <label>Latitude:</label><br>
    <input type="number" name="latitude"
           step="any" min="-90" max="90" required>

    <br><br>

    <label>Longitude:</label><br>
    <input type="number" name="longitude"
           step="any" min="-180" max="180" required>

    <br><br>

    <button type="submit">Record GPS Location</button>

</form>

<h3>Recorded Vehicle Locations</h3>

<table border="1" cellpadding="8">
    <tr>
        <th>Vehicle</th>
        <th>Latitude</th>
        <th>Longitude</th>
        <th>Recorded At</th>
    </tr>

    <?php while ($location = $locations->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($location['registration_number']); ?></td>
            <td><?php echo htmlspecialchars($location['latitude']); ?></td>
            <td><?php echo htmlspecialchars($location['longitude']); ?></td>
            <td><?php echo htmlspecialchars($location['recorded_at']); ?></td>
        </tr>
    <?php endwhile; ?>
</table>

<br>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>