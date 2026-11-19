<?php
error_reporting(E_ALL);
ini_set('display_errors',1);


require_once 'db_connect.php';

$sql = "SELECT 
 vehicles.vehicle_id,
            vehicles.registration_number,
            vehicles.vehicle_model,
            vehicles.capacity,
            vehicles.status,
            users.full_name AS driver_name
        FROM vehicles
        LEFT JOIN drivers
            ON vehicles.driver_id = drivers.driver_id
        LEFT JOIN users
            ON drivers.user_id = users.user_id
        ORDER BY vehicles.vehicle_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Vehicles</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #123456;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 14px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #123456;
            color: white;
        }

        tr:nth-child(even) {
            background: #f2f5f9;
        }

        a {
            color: #123456;
            font-weight: bold;
        }
    </style>
</head>

<body>

<h2>Registered Vehicles</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Registration Number</th>
        <th>Vehicle Model</th>
        <th>Capacity</th>
        <th>Status</th>
        <th>Actions</th>
        <th>Assigned Driver</th>
    </tr>

    <?php if ($result && $result->num_rows > 0): ?>

        <?php while ($vehicle = $result->fetch_assoc()): ?>

            <tr>
                <td><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['registration_number']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['vehicle_model']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['capacity']); ?></td>
                <td><?php echo htmlspecialchars($vehicle['status']); ?></td>
                <td>
    <?php
    echo !empty($vehicle['driver_name'])
        ? htmlspecialchars($vehicle['driver_name'])
        : 'Not Assigned';
    ?>
</td>
                <td>
    <a href="edit_vehicle.php?id=<?php echo $vehicle['vehicle_id']; ?>">
        Edit
    </a>
</td>

    <form method="POST" action="delete_vehicle.php"
          onsubmit="return confirm('Are you sure you want to delete this vehicle?');"
          style="display:inline;">

        <input type="hidden" name="vehicle_id"
               value="<?php echo $vehicle['vehicle_id']; ?>">

        <button type="submit">Delete</button>
    </form>
</td>
            </tr>

        <?php endwhile; ?>

    <?php else: ?>

        <tr>
            <td colspan="5">No vehicles registered yet.</td>
        </tr>

    <?php endif; ?>

</table>

<br>

<a href="vehicles.php">Register Another Vehicle</a> |
<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>