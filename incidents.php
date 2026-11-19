<?php
session_start();
require_once 'db_connect.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    die("Please log in to report or view incidents.");
}

$message = "";
$user_id = (int) $_SESSION['user_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Report a new incident
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['report_incident'])) {

    $vehicle_id = !empty($_POST['vehicle_id'])
        ? (int) $_POST['vehicle_id'] : null;

    $incident_type = trim($_POST['incident_type']);
    $description = trim($_POST['description']);

    if ($incident_type === "" || $description === "") {
        $message = "Please complete all required fields.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO incidents
            (user_id, vehicle_id, incident_type, description)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "iiss",
            $user_id,
            $vehicle_id,
            $incident_type,
            $description
        );

        if ($stmt->execute()) {
            $message = "Incident reported successfully!";
        } else {
            $message = "Error reporting incident: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Allow administrators to update incident status
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {

    if (!$is_admin) {
        die("Access denied.");
    }

    $incident_id = (int) $_POST['incident_id'];
    $status = $_POST['status'];

    $allowed_statuses = ['pending', 'investigating', 'resolved'];

    if (in_array($status, $allowed_statuses, true)) {
        $stmt = $conn->prepare(
            "UPDATE incidents SET status = ? WHERE incident_id = ?"
        );

        $stmt->bind_param("si", $status, $incident_id);

        if ($stmt->execute()) {
            $message = "Incident status updated successfully!";
        } else {
            $message = "Error updating incident: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Get available vehicles
$vehicles = $conn->query(
    "SELECT vehicle_id, registration_number
     FROM vehicles
     ORDER BY registration_number"
);

// Get incidents
if ($is_admin) {
    $incidents = $conn->query(
        "SELECT i.*, u.full_name, v.registration_number
         FROM incidents i
         LEFT JOIN users u ON i.user_id = u.user_id
         LEFT JOIN vehicles v ON i.vehicle_id = v.vehicle_id
         ORDER BY i.reported_at DESC"
    );
} else {
    $stmt = $conn->prepare(
        "SELECT i.*, v.registration_number
         FROM incidents i
         LEFT JOIN vehicles v ON i.vehicle_id = v.vehicle_id
         WHERE i.user_id = ?
         ORDER BY i.reported_at DESC"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $incidents = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Incident Management</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
            padding: 25px;
            color: #333;
        }

        .page-container {
            max-width: 1200px;
            margin: auto;
        }

        .page-header {
            background: #123456;
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 26px;
        }

        .page-header p {
            margin-bottom: 0;
            color: #dce8f5;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }

        h2 {
            color: #123456;
            margin-top: 0;
        }

        label {
            display: block;
            margin: 15px 0 8px;
            font-weight: bold;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd5df;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            resize: vertical;
        }

        button {
            background: #123456;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #1d527f;
        }

        .report-button {
            margin-top: 20px;
        }

        .message {
            background: #e8f4ff;
            color: #123456;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th {
            background: #123456;
            color: white;
            padding: 14px;
            text-align: left;
        }

        td {
            padding: 13px;
            border-bottom: 1px solid #e5e5e5;
        }

        tr:hover {
            background: #f5f8fc;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .pending {
            background: #fff0d5;
            color: #9a6200;
        }

        .investigating {
            background: #dcecff;
            color: #1554a0;
        }

        .resolved {
            background: #d9f5e3;
            color: #18743b;
        }

        .update-form {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .update-form select {
            min-width: 125px;
        }

        .dashboard-link {
            display: inline-block;
            background: #123456;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 10px;
        }

        .dashboard-link:hover {
            background: #1d527f;
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .page-header,
            .card {
                padding: 18px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .update-form {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>

<body>

<div class="page-container">

    <div class="page-header">
        <h1>Incident Management</h1>
        <p>Smart Public Transport Tracking System – Embassava SACCO</p>
    </div>

    <div class="card">

        <h2>Report an Incident</h2>

        <?php if ($message !== ""): ?>
            <p class="message">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <form method="POST">

            <label>Select Vehicle</label>

            <select name="vehicle_id">
                <option value="">Select Vehicle (Optional)</option>

                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <option value="<?php echo $vehicle['vehicle_id']; ?>">
                        <?php echo htmlspecialchars($vehicle['registration_number']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Incident Type</label>

            <select name="incident_type" required>
                <option value="">Select Incident Type</option>
                <option value="Vehicle Breakdown">Vehicle Breakdown</option>
                <option value="Reckless Driving">Reckless Driving</option>
                <option value="Accident">Accident</option>
                <option value="Delay">Delay</option>
                <option value="Other">Other</option>
            </select>

            <label>Description</label>

            <textarea name="description"
                      rows="5"
                      placeholder="Describe what happened..."
                      required></textarea>

            <button type="submit" name="report_incident"
                    class="report-button">
                Report Incident
            </button>

        </form>

    </div>

    <div class="card">

        <h2>
            <?php echo $is_admin ? "All Reported Incidents" : "My Reported Incidents"; ?>
        </h2>

        <div class="table-wrapper">

            <table>

                <tr>
                    <?php if ($is_admin): ?>
                        <th>Reported By</th>
                    <?php endif; ?>

                    <th>Vehicle</th>
                    <th>Incident Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date Reported</th>

                    <?php if ($is_admin): ?>
                        <th>Update Status</th>
                    <?php endif; ?>
                </tr>

                <?php while ($incident = $incidents->fetch_assoc()): ?>

                    <tr>

                        <?php if ($is_admin): ?>
                            <td>
                                <?php echo htmlspecialchars($incident['full_name'] ?? 'Unknown'); ?>
                            </td>
                        <?php endif; ?>

                        <td>
                            <?php echo htmlspecialchars($incident['registration_number'] ?? 'Not specified'); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($incident['incident_type']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($incident['description']); ?>
                        </td>

                        <td>
                            <span class="status <?php echo htmlspecialchars($incident['status']); ?>">
                                <?php echo htmlspecialchars($incident['status']); ?>
                            </span>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($incident['reported_at']); ?>
                        </td>

                        <?php if ($is_admin): ?>
                            <td>

                                <form method="POST" class="update-form">

                                    <input type="hidden"
                                           name="incident_id"
                                           value="<?php echo $incident['incident_id']; ?>">

                                    <select name="status">
                                        <option value="pending">Pending</option>
                                        <option value="investigating">Investigating</option>
                                        <option value="resolved">Resolved</option>
                                    </select>

                                    <button type="submit" name="update_status">
                                        Update
                                    </button>

                                </form>

                            </td>
                        <?php endif; ?>

                    </tr>

                <?php endwhile; ?>

            </table>

        </div>

    </div>

    <a href="dashboard.php" class="dashboard-link">
        Back to Dashboard
    </a>

</div>

</body>
</html>