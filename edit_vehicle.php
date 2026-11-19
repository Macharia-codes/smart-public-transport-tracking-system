<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can edit vehicles.");
}

require_once 'db_connect.php';

$message = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid vehicle ID.");
}

$vehicle_id = (int) $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM vehicles WHERE vehicle_id = ?");
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    die("Vehicle not found.");
}

$vehicle = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $registration_number = strtoupper(trim($_POST['registration_number']));
    $vehicle_model = trim($_POST['vehicle_model']);
    $capacity = (int) $_POST['capacity'];
    $status = $_POST['status'];

    if ($registration_number === "" || $vehicle_model === "" || $capacity <= 0) {
        $message = "Please enter valid vehicle details.";
    } elseif (!in_array($status, ['active', 'inactive', 'maintenance'], true)) {
        $message = "Invalid vehicle status.";
    } else {

        $update = $conn->prepare(
            "UPDATE vehicles
             SET registration_number = ?, vehicle_model = ?, capacity = ?, status = ?
             WHERE vehicle_id = ?"
        );

        $update->bind_param(
            "ssisi",
            $registration_number,
            $vehicle_model,
            $capacity,
            $status,
            $vehicle_id
        );

        if ($update->execute()) {
            header("Location: view_vehicles.php");
            exit;
        } else {
            $message = "Update failed. Check whether the registration number already exists.";
        }

        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Vehicle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
        }

        .container {
            width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
        }

        input, select, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        button {
            background: #123456;
            color: white;
            border: none;
            cursor: pointer;
        }

        .message {
            color: red;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Edit Vehicle</h2>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST">

        <label>Registration Number</label>
        <input type="text" name="registration_number"
               value="<?php echo htmlspecialchars($vehicle['registration_number']); ?>"
               required>

        <label>Vehicle Model</label>
        <input type="text" name="vehicle_model"
               value="<?php echo htmlspecialchars($vehicle['vehicle_model']); ?>"
               required>

        <label>Passenger Capacity</label>
        <input type="number" name="capacity" min="1"
               value="<?php echo htmlspecialchars($vehicle['capacity']); ?>"
               required>

        <label>Status</label>
        <select name="status">
            <option value="active" <?php if ($vehicle['status'] === 'active') echo 'selected'; ?>>Active</option>
            <option value="inactive" <?php if ($vehicle['status'] === 'inactive') echo 'selected'; ?>>Inactive</option>
            <option value="maintenance" <?php if ($vehicle['status'] === 'maintenance') echo 'selected'; ?>>Maintenance</option>
        </select>

        <button type="submit">Update Vehicle</button>

    </form>

    <p><a href="view_vehicles.php">Back to Vehicles</a></p>

</div>

</body>
</html>