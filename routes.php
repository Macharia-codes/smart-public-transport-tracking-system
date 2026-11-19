<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can manage routes.");
}

require_once 'db_connect.php';

$message = "";
$message_type = "";

// Register a new route
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $route_name = trim($_POST['route_name']);
    $start_location = trim($_POST['start_location']);
    $end_location = trim($_POST['end_location']);
    $distance_km = $_POST['distance_km'];

    if ($route_name === "" || $start_location === "" || $end_location === "") {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } elseif ($distance_km !== "" &&
        (!is_numeric($distance_km) || $distance_km <= 0)) {

        $message = "Please enter a valid route distance.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO routes
            (route_name, start_location, end_location, distance_km)
            VALUES (?, ?, ?, ?)"
        );

        $distance = $distance_km === "" ? null : (float)$distance_km;

        $stmt->bind_param(
            "sssd",
            $route_name,
            $start_location,
            $end_location,
            $distance
        );

        if ($stmt->execute()) {
            $message = "Route registered successfully!";
            $message_type = "success";
        } else {
            $message = "Route registration failed: " . $stmt->error;
            $message_type = "error";
        }

        $stmt->close();
    }
}

// Retrieve registered routes
$result = $conn->query(
    "SELECT * FROM routes ORDER BY route_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Route Management</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #F2F5F9;
            color: #0B2545;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .page-header {
            background: #0B2545;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .page-header h2 {
            margin-bottom: 8px;
        }

        .page-header p {
            color: #DCE6F1;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .card h3 {
            color: #123456;
            margin-bottom: 20px;
            border-bottom: 2px solid #E8EEF5;
            padding-bottom: 12px;
        }

        .message {
            padding: 13px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .success {
            background: #DFF5E5;
            color: #176B35;
        }

        .error {
            background: #FCE4E4;
            color: #A32020;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #CCD6E0;
            border-radius: 6px;
            font-size: 15px;
        }

        input:focus {
            outline: none;
            border-color: #123456;
            box-shadow: 0 0 4px rgba(18,52,86,0.2);
        }

        button {
            background: #123456;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
        }

        button:hover {
            background: #0B2545;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #0B2545;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #E8EEF5;
        }

        tr:nth-child(even) {
            background: #F7F9FC;
        }

        tr:hover {
            background: #E8EEF5;
        }

        .status {
            background: #DFF5E5;
            color: #176B35;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 13px;
        }

        .back-link {
            display: inline-block;
            background: #123456;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 5px;
        }

        .back-link:hover {
            background: #0B2545;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 15px;
            }
        }

    </style>

</head>

<body>

<div class="container">

    <div class="page-header">
        <h2>Transport Route Management</h2>
        <p>Register and manage public transport routes.</p>
    </div>

    <?php if ($message !== ""): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card">

        <h3>Register New Route</h3>

        <form method="POST">

            <div class="form-group">
                <label>Route Name:</label>
                <input type="text" name="route_name" required>
            </div>

            <div class="form-group">
                <label>Starting Location:</label>
                <input type="text" name="start_location" required>
            </div>

            <div class="form-group">
                <label>Ending Location:</label>
                <input type="text" name="end_location" required>
            </div>

            <div class="form-group">
                <label>Distance (Kilometres):</label>
                <input type="number" name="distance_km"
                       step="0.01" min="0.01">
            </div>

            <button type="submit">Register Route</button>

        </form>

    </div>

    <div class="card">

        <h3>Registered Routes</h3>

        <div class="table-wrapper">

            <table>

                <tr>
                    <th>Route ID</th>
                    <th>Route Name</th>
                    <th>Starting Location</th>
                    <th>Ending Location</th>
                    <th>Distance (KM)</th>
                    <th>Status</th>
                </tr>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($route = $result->fetch_assoc()): ?>

                        <tr>
                            <td><?php echo htmlspecialchars($route['route_id']); ?></td>

                            <td><?php echo htmlspecialchars($route['route_name']); ?></td>

                            <td><?php echo htmlspecialchars($route['start_location']); ?></td>

                            <td><?php echo htmlspecialchars($route['end_location']); ?></td>

                            <td><?php echo htmlspecialchars($route['distance_km'] ?? 'Not specified'); ?></td>

                            <td>
                                <span class="status">
                                    <?php echo htmlspecialchars($route['status']); ?>
                                </span>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="6">No routes registered yet.</td>
                    </tr>

                <?php endif; ?>

            </table>

        </div>

    </div>

    <a href="dashboard.php" class="back-link">
        Back to Dashboard
    </a>

</div>

</body>
</html>