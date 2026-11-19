<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can view registered drivers.");
}

require_once 'db_connect.php';

$sql = "SELECT 
            drivers.driver_id,
            users.full_name,
            users.email,
            users.phone,
            drivers.license_number,
            drivers.status
        FROM drivers
        LEFT JOIN users ON drivers.user_id = users.user_id
        ORDER BY drivers.driver_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Drivers</title>
</head>
<body>

<h2>Registered Drivers</h2>

<table border="1" cellpadding="10" cellspacing="0">
    <tr>
        <th>Driver ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Licence Number</th>
        <th>Status</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>

        <?php while ($driver = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($driver['driver_id']); ?></td>
                <td><?php echo htmlspecialchars($driver['full_name'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($driver['email'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($driver['phone'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($driver['license_number']); ?></td>
                <td><?php echo htmlspecialchars($driver['status']); ?></td>
            </tr>
        <?php endwhile; ?>

    <?php else: ?>
        <tr>
            <td colspan="6">No drivers registered yet.</td>
        </tr>
    <?php endif; ?>

</table>

<br>
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>