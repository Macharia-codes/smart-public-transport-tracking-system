<?php
require_once 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $registration_number = strtoupper(trim($_POST['registration_number']));
    $vehicle_model = trim($_POST['vehicle_model']);
    $capacity = (int) $_POST['capacity'];
    $status = $_POST['status'];

    if ($registration_number == "" || $vehicle_model == "" || $capacity <= 0) {

        $message = "Please enter valid vehicle details.";

    } else {

        try {

            $stmt = $conn->prepare(
                "INSERT INTO vehicles
                (registration_number, vehicle_model, capacity, status)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssis",
                $registration_number,
                $vehicle_model,
                $capacity,
                $status
            );

            $stmt->execute();

            $message = "Vehicle registered successfully!";

            $stmt->close();

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1062) {
                $message = "This vehicle registration number already exists.";
            } else {
                $message = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
        }

        .container {
            width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ccc;
        }

        h2 {
            text-align: center;
            color: #123456;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #123456;
            color: white;
            border: none;
            cursor: pointer;
        }

        .message {
            text-align: center;
            color: green;
        }
        /* Responsive vehicle management */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .container {
        width: 100%;
        max-width: 100%;
        padding: 15px;
        box-sizing: border-box;
    }

    form {
        width: 100%;
    }

    input,
    select,
    button {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        margin-bottom: 10px;
    }

    table {
        display: block;
        width: 100%;
        overflow-x: auto;
        white-space: nowrap;
    }
}
    </style>
</head>

<body>

<div class="container">

    <h2>Register a Vehicle</h2>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST">

        <input type="text"
               name="registration_number"
               placeholder="Vehicle Registration Number"
               required>

        <input type="text"
               name="vehicle_model"
               placeholder="Vehicle Model"
               required>

        <input type="number"
               name="capacity"
               placeholder="Passenger Capacity"
               min="1"
               required>

        <select name="status" required>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="maintenance">Maintenance</option>
        </select>

        <button type="submit">Register Vehicle</button>

    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>