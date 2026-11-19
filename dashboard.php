<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$full_name = htmlspecialchars($_SESSION['full_name']);
$role = $_SESSION['role'];
$is_admin = ($role === 'admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - Smart Transport Tracking</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
        }

        header {
            background: #123456;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .container {
            width: 85%;
            max-width: 1100px;
            margin: 35px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 20px;
            margin-top: 25px;
        }

        .card {
            background: #e8f0f8;
            padding: 22px;
            border-radius: 8px;
            transition: 0.3s;
        }

        .card:hover {
            background: #d4e5f5;
            transform: translateY(-4px);
        }

        .card a {
            color: #123456;
            text-decoration: none;
            display: block;
        }

        .card h3 {
            margin-top: 0;
        }

        .card p {
            color: #444;
        }

        .logout {
            display: inline-block;
            background: #c0392b;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 25px;
        }

        .logout:hover {
            background: #922b21;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        /* Responsive dashboard */
@media (max-width: 768px) {
    body {
        padding: 10px;
    }

    .dashboard-container {
        width: 100%;
        max-width: 100%;
        padding: 15px;
        margin: 10px auto;
        box-sizing: border-box;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-card {
        width: 100%;
        box-sizing: border-box;
    }

    button,
    .btn,
    a.button {
        max-width: 100%;
        box-sizing: border-box;
    }
}
    </style>
</head>

<body>

<header>
    <h1>Smart Public Transport Tracking System</h1>
    <p>Embassava SACCO - Nairobi</p>
</header>

<div class="container">

    <h2>Welcome, <?php echo $full_name; ?>!</h2>

    <p>You have successfully accessed your dashboard.</p>

    <p>
        Account Role:
        <strong><?php echo htmlspecialchars($role); ?></strong>
    </p>

    <h2>System Modules</h2>

    <div class="grid">

        <?php if ($is_admin): ?>

            <div class="card">
                <a href="routes.php">
                    <h3>Route Management</h3>
                    <p>Register and manage transport routes.</p>
                </a>
            </div>

            <div class="card">
                <a href="drivers.php">
                    <h3>Driver Management</h3>
                    <p>Register and manage drivers.</p>
                </a>
            </div>

            <div class="card">
                <a href="vehicles.php">
                    <h3>Vehicle Management</h3>
                    <p>Register and manage transport vehicles.</p>
                </a>
            </div>

            <div class="card">
                <a href="assign_vehicle.php">
                    <h3>Vehicle Assignment</h3>
                    <p>Assign vehicles to transport routes.</p>
                </a>
            </div>

            <div class="card">
                <a href="track_vehicle.php">
                    <h3>vehicle tracking</h3>
                    <p>view recorded vehicle locations and assigned routes.</p>
                </a>
            </div>

            <div class="card">
                <a href="incidents.php">
                    <h3>Incident Management</h3>
                    <p>View reported incidents and update their status.</p>
                </a>
            </div>

        <?php else: ?>

            <div class="card">
                <a href="track_vehicle.php">
                    <h3>Vehicle Tracking</h3>
                    <p>View recorded vehicle locations.</p>
                </a>
            </div>

            <div class="card">
                <a href="incidents.php">
                    <h3>Report an Incident</h3>
                    <p>Report transport-related incidents.</p>
                </a>
            </div>

        <?php endif; ?>

    </div>

    <a class="logout" href="logout.php">Logout</a>

</div>

<footer>
    <p>&copy; 2026 Smart Public Transport Tracking System</p>
</footer>

</body>
</html>