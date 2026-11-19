<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Smart Public Transport Tracking System</title>

   <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background: #F2F5F9;
        color: #263238;
        line-height: 1.6;
    }

    header {
        background: #123456;
        color: white;
        padding: 20px;
        text-align: center;
    }

    header h1 {
        font-size: 28px;
        margin-bottom: 5px;
    }

    nav {
        background: #0B2545;
        padding: 12px;
        text-align: center;
    }

    nav a {
        color: white;
        text-decoration: none;
        margin: 0 12px;
        font-weight: bold;
        display: inline-block;
    }

    nav a:hover {
        color: #FFD166;
    }

    .hero {
        text-align: center;
        padding: 75px 20px;
        background: linear-gradient(135deg, #E8EEF5, #FFFFFF);
    }

    .hero h2 {
        font-size: 34px;
        color: #123456;
        margin-bottom: 15px;
    }

    .hero p {
        max-width: 650px;
        margin: 0 auto 25px;
        font-size: 17px;
    }

    .btn {
        display: inline-block;
        background: #123456;
        color: white;
        padding: 12px 25px;
        text-decoration: none;
        border-radius: 6px;
        margin: 5px;
    }

    .btn:hover {
        background: #0B2545;
    }

    .features {
        max-width: 1100px;
        margin: 40px auto;
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .feature-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .feature-card h3 {
        color: #123456;
        margin-bottom: 10px;
    }

    footer {
        background: #0B2545;
        color: white;
        text-align: center;
        padding: 18px;
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        header h1 {
            font-size: 23px;
        }

        .hero h2 {
            font-size: 27px;
        }

        .features {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        header h1 {
            font-size: 20px;
        }

        nav a {
            display: block;
            margin: 10px 0;
        }

        .hero {
            padding: 45px 15px;
        }

        .hero h2 {
            font-size: 23px;
        }

        .hero p {
            font-size: 15px;
        }

        .features {
            grid-template-columns: 1fr;
            padding: 15px;
        }

        .btn {
            display: block;
            width: 100%;
            margin: 10px 0;
        }
    }
</style>
</head>

<body>

<header>
    <h1>Smart Public Transport Tracking System</h1>
    <p>Embassava SACCO – Nairobi</p>
</header>

<nav>
    <a href="index.php">Home</a>
    <a href="login.php">Login</a>
    <a href="register.php">Register</a>
</nav>

<section class="hero">
    <h2>Welcome to Smart Transport Tracking</h2>

    <p>
        A smart transport management system designed to improve
        vehicle tracking, passenger safety and public transport
        operations at Embassava SACCO.
    </p>

    <a href="login.php" class="btn">Login to System</a>
    <a href="register.php" class="btn">Create Account</a>
</section>

<section class="features">

    <div class="feature-card">
        <h3>Vehicle Tracking</h3>
        <p>Monitor vehicle locations and access tracking information.</p>
    </div>

    <div class="feature-card">
        <h3>Driver Management</h3>
        <p>Manage drivers and their vehicle assignments.</p>
    </div>

    <div class="feature-card">
        <h3>Incident Reporting</h3>
        <p>Report and monitor transport-related incidents.</p>
    </div>

</section>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Embassava SACCO. All Rights Reserved.</p>
</footer>

</body>
</html>