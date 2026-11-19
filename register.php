<?php
include 'db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($full_name) || empty($email) || empty($password)) {
        $message = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";

    } else {

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, phone, password, role)
                 VALUES (?, ?, ?, ?, 'passenger')"
            );

            $stmt->bind_param(
                "ssss",
                $full_name,
                $email,
                $phone,
                $hashed_password
            );

            $stmt->execute();
            $stmt->close();

            header("Location: login.php?registered=1");
            exit;

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1062) {
                $message = "This email address is already registered. Please use another email or log in.";
            } else {
                $message = "Registration could not be completed. Please try again.";
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

    <title>Passenger Registration</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
        }

        .container {
            width: 350px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px #ccc;
        }

        h2 {
            text-align: center;
            color: #123456;
        }

        input {
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
            color: red;
            text-align: center;
        }
        /* Responsive registration page */
@media (max-width: 768px) {
    body {
        padding: 15px;
    }

    .register-container {
        width: 100%;
        max-width: 450px;
        margin: 20px auto;
        padding: 20px;
    }

    input,
    select,
    button {
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
}
    </style>
</head>

<body>

<div class="container">

    <h2>Passenger Registration</h2>

    <p class="message">
        <?php echo htmlspecialchars($message); ?>
    </p>

    <form method="POST" action="">

        <input type="text"
               name="full_name"
               placeholder="Full Name"
               required>

        <input type="email"
               name="email"
               placeholder="Email Address"
               required>

        <input type="text"
               name="phone"
               placeholder="Phone Number">

        <input type="password"
               name="password"
               placeholder="Password (minimum 8 characters)"
               minlength="8"
               required>

        <button type="submit">Register</button>

    </form>

    <p>Already have an account?
        <a href="login.php">Login here</a>
    </p>

</div>

</body>
</html>