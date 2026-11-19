<?php
session_start();
require_once 'db_connect.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get vehicles, latest GPS locations and latest route assignments
$sql = "
    SELECT
        v.vehicle_id,
        v.registration_number,
        v.vehicle_model,
        v.capacity,
        v.status,
        r.route_name,
        r.start_location,
        r.end_location,
        g.latitude,
        g.longitude,
        g.recorded_at
    FROM vehicles v
    LEFT JOIN (
        SELECT vehicle_id, MAX(assignment_id) AS latest_assignment
        FROM vehicle_assignments
        GROUP BY vehicle_id
    ) latest_assignment
        ON v.vehicle_id = latest_assignment.vehicle_id
    LEFT JOIN vehicle_assignments va
        ON latest_assignment.latest_assignment = va.assignment_id
    LEFT JOIN routes r
        ON va.route_id = r.route_id
    LEFT JOIN (
        SELECT vehicle_id, MAX(location_id) AS latest_location
        FROM gps_locations
        GROUP BY vehicle_id
    ) latest_location
        ON v.vehicle_id = latest_location.vehicle_id
    LEFT JOIN gps_locations g
        ON latest_location.latest_location = g.location_id
    ORDER BY v.registration_number
";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load vehicle information.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Vehicle Tracking | Embassava SACCO</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #F2F5F9;
            margin: 0;
            padding: 25px 15px;
            color: #2B3445;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            background: #0B2545;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header h2 {
            margin: 0 0 10px;
            font-size: 25px;
        }

        .header p {
            margin: 0;
        }

        .note {
            background: #E8EEF5;
            border-left: 4px solid #123456;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .table-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 12px;
            border: 1px solid #E2E8F0;
            text-align: left;
        }

        th {
            background: #0B2545;
            color: white;
        }

        tr:hover {
            background: #F2F5F9;
        }

        .mobile-cards {
            display: none;
        }

        .vehicle-card {
            background: white;
            padding: 20px;
            margin-bottom: 18px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            overflow-wrap: anywhere;
        }

        .vehicle-card h3 {
            color: #123456;
            margin-top: 0;
            border-bottom: 2px solid #E8EEF5;
            padding-bottom: 10px;
        }

        .map-button {
            display: inline-block;
            background: #123456;
            color: white;
            padding: 11px 18px;
            text-decoration: none;
            border-radius: 6px;
        }

        .map-button:hover {
            background: #0B2545;
        }

        .back {
            display: inline-block;
            background: #123456;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
        }

        .back:hover {
            background: #0B2545;
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .header h2 {
                font-size: 21px;
            }

            .desktop-table {
                display: none;
            }

            .mobile-cards {
                display: block;
            }

            .table-card {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Smart Public Transport Vehicle Tracking</h2>
        <p>Embassava SACCO – Nairobi</p>
    </div>

    <p>View registered vehicles and their latest recorded locations.</p>

    <div class="note">
        Location information is based on GPS coordinates recorded in the system.
    </div>

    <!-- Desktop vehicle table -->
    <div class="table-card desktop-table">
        <div class="table-wrapper">

            <table>
                <tr>
                    <th>Registration</th>
                    <th>Model</th>
                    <th>Capacity</th>
                    <th>Assigned Route</th>
                    <th>Status</th>
                    <th>Latitude</th>
                    <th>Longitude</th>
                    <th>Last Recorded</th>
                    <th>Map</th>
                </tr>

                <?php if ($result->num_rows > 0): ?>
                    <?php while ($vehicle = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($vehicle['registration_number']) ?></td>
                            <td><?= htmlspecialchars($vehicle['vehicle_model'] ?? 'Not specified') ?></td>
                            <td><?= htmlspecialchars($vehicle['capacity'] ?? 'Not specified') ?></td>
                            <td><?= htmlspecialchars($vehicle['route_name'] ?? 'Not assigned') ?></td>
                            <td><?= htmlspecialchars($vehicle['status']) ?></td>
                            <td><?= htmlspecialchars($vehicle['latitude'] ?? 'Not recorded') ?></td>
                            <td><?= htmlspecialchars($vehicle['longitude'] ?? 'Not recorded') ?></td>
                            <td><?= htmlspecialchars($vehicle['recorded_at'] ?? 'Not recorded') ?></td>
                            <td>
                                <?php if ($vehicle['latitude'] !== null && $vehicle['longitude'] !== null): ?>
                                    <a target="_blank" rel="noopener noreferrer"
                                       href="https://www.openstreetmap.org/?mlat=<?= urlencode($vehicle['latitude']) ?>&mlon=<?= urlencode($vehicle['longitude']) ?>#map=16/<?= urlencode($vehicle['latitude']) ?>/<?= urlencode($vehicle['longitude']) ?>">
                                        View Map
                                    </a>
                                <?php else: ?>
                                    No location
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No vehicles have been registered yet.</td>
                    </tr>
                <?php endif; ?>

            </table>
        </div>
    </div>

    <!-- Mobile vehicle cards -->
    <div class="mobile-cards">

        <?php
        if ($result->num_rows > 0):
            $result->data_seek(0);

            while ($vehicle = $result->fetch_assoc()):
        ?>

            <div class="vehicle-card">

                <h3><?= htmlspecialchars($vehicle['registration_number']) ?></h3>

                <p><strong>Model:</strong>
                    <?= htmlspecialchars($vehicle['vehicle_model'] ?? 'Not specified') ?>
                </p>

                <p><strong>Capacity:</strong>
                    <?= htmlspecialchars($vehicle['capacity'] ?? 'Not specified') ?>
                </p>

                <p><strong>Route:</strong>
                    <?= htmlspecialchars($vehicle['route_name'] ?? 'Not assigned') ?>
                </p>

                <p><strong>Status:</strong>
                    <?= htmlspecialchars($vehicle['status']) ?>
                </p>

                <p><strong>Latitude:</strong>
                    <?= htmlspecialchars($vehicle['latitude'] ?? 'Not recorded') ?>
                </p>

                <p><strong>Longitude:</strong>
                    <?= htmlspecialchars($vehicle['longitude'] ?? 'Not recorded') ?>
                </p>

                <p><strong>Last Recorded:</strong>
                    <?= htmlspecialchars($vehicle['recorded_at'] ?? 'Not recorded') ?>
                </p>

                <?php if ($vehicle['latitude'] !== null && $vehicle['longitude'] !== null): ?>

                    <a class="map-button" target="_blank" rel="noopener noreferrer"
                       href="https://www.openstreetmap.org/?mlat=<?= urlencode($vehicle['latitude']) ?>&mlon=<?= urlencode($vehicle['longitude']) ?>#map=16/<?= urlencode($vehicle['latitude']) ?>/<?= urlencode($vehicle['longitude']) ?>">
                        View Map
                    </a>

                <?php else: ?>
                    <p>No location available</p>
                <?php endif; ?>

            </div>

        <?php
            endwhile;
        else:
        ?>
            <p>No vehicles have been registered yet.</p>
        <?php endif; ?>

    </div>

    <a class="back" href="dashboard.php">← Back to Dashboard</a>

</div>

</body>
</html>