<?php
session_start();
require_once 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT user_id, full_name, email, password, role
         FROM users
          WHERE LOWER(TRIM(email)) = LOWER(TRIM(?))"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    echo "Database connected. Matching accounts " . $result->num_rows;

    if ($result->num_rows === 1) {

        $user = $result->fetch_assoc();

         if (password_verify($password, $user['password'])) {


            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Incorrect email or password.";
        }

    } else {
        $error = "Incorrect email or password.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Transport Tracking</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
        }

        .container {
            width: 350px;
            margin: 70px auto;
            padding: 30px;
            background: white;
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

        .error {
            color: red;
            text-align: center;
        }

        .success {
            color: green;
            text-align: center;
        }
        /* Responsive login page */
@media (max-width: 768px) {
    body {
        padding: 15px;
    }

    .login-container {
        width: 100%;
        max-width: 420px;
        margin: 20px auto;
        padding: 20px;
    }

    input,
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

    <h2>Account Login</h2>

    <?php if (isset($_GET['registered'])): ?>
        <p class="success">Registration successful! Please log in.</p>
    <?php endif; ?>

    <p class="error">
        <?php echo htmlspecialchars($error); ?>
    </p>

    <form method="POST" action="">

        <input type="email"
               name="email"
               placeholder="Email Address"
               required>

        <input type="password"
               name="password"
               placeholder="Password"
               required>

        <button type="submit">Login</button>

    </form>

    <p>Don't have an account?
        <a href="register.php">Register here</a>
    </p>

</div>

</body>
</html>