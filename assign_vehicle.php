<?php
session_start();
require_once 'db_connect.php';

// Only administrators can assign vehicles
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Administrators only.");
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $vehicle_id = (int) $_POST['vehicle_id'];
    $route_id = (int) $_POST['route_id'];

    $check = $conn->prepare(
        "SELECT vehicle_id FROM vehicles WHERE vehicle_id = ?"
    );
    $check->bind_param("i", $vehicle_id);
    $check->execute();
    $vehicle = $check->get_result();

    $checkRoute = $conn->prepare(
        "SELECT route_id FROM routes WHERE route_id = ?"
    );
    $checkRoute->bind_param("i", $route_id);
    $checkRoute->execute();
    $route = $checkRoute->get_result();

    if ($vehicle->num_rows === 0 || $route->num_rows === 0) {
        $message = "Please select a valid vehicle and route.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO vehicle_assignments (vehicle_id, route_id)
             VALUES (?, ?)"
        );
        $stmt->bind_param("ii", $vehicle_id, $route_id);

        if ($stmt->execute()) {
            $message = "Vehicle assigned to route successfully!";
            $messageType = "success";
        } else {
            $message = "Assignment failed. Please try again.";
            $messageType = "error";
        }

        $stmt->close();
    }

    $check->close();
    $checkRoute->close();
}

$vehicles = $conn->query(
    "SELECT vehicle_id, registration_number
     FROM vehicles ORDER BY registration_number"
);

$routes = $conn->query(
    "SELECT route_id, route_name
     FROM routes ORDER BY route_name"
);

$assignments = $conn->query(
    "SELECT va.assignment_id, v.registration_number,
            r.route_name, va.assigned_at
     FROM vehicle_assignments va
     JOIN vehicles v ON va.vehicle_id = v.vehicle_id
     JOIN routes r ON va.route_id = r.route_id
     ORDER BY va.assigned_at DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Vehicle | Embassava SACCO</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 30px 15px;
            background: #F2F5F9;
            color: #2B3445;
        }

        .container {
            max-width: 950px;
            margin: 0 auto;
        }

        .header {
            background: #0B2545;
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 0;
            font-size: 26px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        h3 {
            color: #123456;
            margin-top: 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #123456;
        }

        select {
            width: 100%;
            padding: 13px;
            border: 1px solid #CBD5E1;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 15px;
            background: white;
        }

        select:focus {
            outline: none;
            border-color: #123456;
        }

        button {
            background: #123456;
            color: white;
            border: none;
            padding: 13px 22px;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background: #0B2545;
        }

        .message {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #D1FAE5;
            color: #065F46;
        }

        .error {
            background: #FEE2E2;
            color: #991B1B;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #E2E8F0;
        }

        th {
            background: #0B2545;
            color: white;
        }

        tr:hover {
            background: #F2F5F9;
        }

        .back-link {
            display: inline-block;
            background: #123456;
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            border-radius: 6px;
        }

        .back-link:hover {
            background: #0B2545;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px 10px;
            }

            .card {
                padding: 18px;
            }

            .header h2 {
                font-size: 21px;
            }

            th, td {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h2>Assign Vehicle to Route</h2>
        <p>Embassava SACCO Transport Management System</p>
    </div>

    <div class="card">

        <h3>Vehicle and Route Assignment</h3>

        <?php if ($message !== ""): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Select Vehicle:</label>

            <select name="vehicle_id" required>
                <option value="">Choose Vehicle</option>

                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <option value="<?php echo $vehicle['vehicle_id']; ?>">
                        <?php echo htmlspecialchars($vehicle['registration_number']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Select Route:</label>

            <select name="route_id" required>
                <option value="">Choose Route</option>

                <?php while ($route = $routes->fetch_assoc()): ?>
                    <option value="<?php echo $route['route_id']; ?>">
                        <?php echo htmlspecialchars($route['route_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">Assign Vehicle</button>

        </form>

    </div>

    <div class="card">

        <h3>Existing Vehicle Assignments</h3>

        <div class="table-wrapper">

            <table>
                <tr>
                    <th>Vehicle</th>
                    <th>Route</th>
                    <th>Assigned At</th>
                </tr>

                <?php while ($assignment = $assignments->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['route_name']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['assigned_at']); ?></td>
                    </tr>
                <?php endwhile; ?>

            </table>

        </div>

    </div>

    <a href="dashboard.php" class="back-link">← Back to Dashboard</a>

</div>

</body>
</html>