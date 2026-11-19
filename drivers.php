<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Only administrators can register drivers.");
}

require_once 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $license_number = strtoupper(trim($_POST['license_number']));

    if ($full_name === "" || $email === "" || $license_number === "") {
        $message = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } else {

        $conn->begin_transaction();

        try {
            $password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

            $user_stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, phone, password, role)
                 VALUES (?, ?, ?, ?, 'driver')"
            );

            $user_stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $phone,
                $password
            );

            $user_stmt->execute();
            $user_id = $conn->insert_id;
            $user_stmt->close();

            $driver_stmt = $conn->prepare(
                "INSERT INTO drivers (user_id, license_number, status)
                 VALUES (?, ?, 'active')"
            );

            $driver_stmt->bind_param("is", $user_id, $license_number);
            $driver_stmt->execute();
            $driver_stmt->close();

            $conn->commit();
            $message = "Driver registered successfully!";

        } catch (Exception $e) {
            $conn->rollback();
            $message = "Registration failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Driver Management</title>

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

        input, button {
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
            text-align: center;
            color: green;
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

    </style>
</head>

<body>

<div class="container">

    <h2>Register Driver</h2>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST">

        <input type="text" name="full_name"
               placeholder="Driver Full Name" required>

        <input type="email" name="email"
               placeholder="Driver Email Address" required>

        <input type="text" name="phone"
               placeholder="Phone Number">

        <input type="text" name="license_number"
               placeholder="Driving Licence Number" required>

        <button type="submit">Register Driver</button>

    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>

</div>

</body>
</html>