<?php
session_start();

require_once "db_connect.php";

// Check whether the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only drivers are allowed
if ($_SESSION['role'] !== 'driver') {
    die("Access denied. This page is for drivers only.");
}

$user_id = $_SESSION['user_id'];

// Find the driver's record
$stmt = $conn->prepare(
    "SELECT driver_id FROM drivers WHERE user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No driver record found for this account.");
}

$driver = $result->fetch_assoc();
$driver_id = $driver['driver_id'];

// Find the vehicle assigned to this driver
$stmt = $conn->prepare(
    "SELECT vehicle_id, registration_number
     FROM vehicles
     WHERE driver_id = ? AND status = 'active'
     LIMIT 1"
);

$stmt->bind_param("i", $driver_id);
$stmt->execute();

$vehicle_result = $stmt->get_result();

if ($vehicle_result->num_rows === 0) {
    die("No active vehicle is assigned to you. Contact the administrator.");
}

$vehicle = $vehicle_result->fetch_assoc();
$vehicle_id = $vehicle['vehicle_id'];

// Receive GPS coordinates from JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header("Content-Type: application/json");

    $data = json_decode(file_get_contents("php://input"), true);

    if (
        !isset($data['latitude']) ||
        !isset($data['longitude'])
    ) {
        echo json_encode([
            "success" => false,
            "message" => "GPS coordinates are missing."
        ]);
        exit();
    }

    $latitude = filter_var($data['latitude'], FILTER_VALIDATE_FLOAT);
    $longitude = filter_var($data['longitude'], FILTER_VALIDATE_FLOAT);

    if (
        $latitude === false ||
        $longitude === false ||
        $latitude < -90 || $latitude > 90 ||
        $longitude < -180 || $longitude > 180
    ) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid GPS coordinates."
        ]);
        exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO gps_locations
        (vehicle_id, latitude, longitude)
        VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "idd",
        $vehicle_id,
        $latitude,
        $longitude
    );

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Location updated successfully."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Could not save location."
        ]);
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Driver GPS Tracking</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f4f7;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        h2 {
            color: #176b3a;
        }

        button {
            background: #176b3a;
            color: white;
            border: none;
            padding: 14px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            margin: 8px;
        }

        button.stop {
            background: #c62828;
        }

        #status {
            margin-top: 20px;
            padding: 12px;
            background: #eeeeee;
            border-radius: 6px;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Driver GPS Tracking</h2>

    <p>
        Vehicle:
        <strong><?php echo htmlspecialchars($vehicle['registration_number']); ?></strong>
    </p>

    <p>Use your phone's location to share your vehicle's position.</p>

    <button onclick="startTracking()">Start Tracking</button>

    <button class="stop" onclick="stopTracking()">Stop Tracking</button>

    <div id="status">
        Tracking has not started.
    </div>

</div>

<script>

let watchID = null;

function startTracking() {

    if (!navigator.geolocation) {
        document.getElementById("status").innerHTML =
            "GPS is not supported by this browser.";
        return;
    }

    document.getElementById("status").innerHTML =
        "Requesting GPS permission...";

    watchID = navigator.geolocation.watchPosition(

        function(position) {

            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            document.getElementById("status").innerHTML =
                "GPS detected. Saving location...";

            fetch("driver_tracking.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    latitude: latitude,
                    longitude: longitude
                })
            })

            .then(response => response.json())

            .then(data => {

                document.getElementById("status").innerHTML =
                    data.message +
                    "<br>Latitude: " + latitude +
                    "<br>Longitude: " + longitude;

            })

            .catch(error => {

                document.getElementById("status").innerHTML =
                    "Error saving GPS location.";

            });

        },

        function(error) {

            document.getElementById("status").innerHTML =
                "GPS error: " + error.message;

        },

        {
            enableHighAccuracy: true,
            maximumAge: 5000,
            timeout: 15000
        }

    );
}

function stopTracking() {

    if (watchID !== null) {

        navigator.geolocation.clearWatch(watchID);

        watchID = null;

        document.getElementById("status").innerHTML =
            "GPS tracking stopped.";

    }
}

</script>

</body>
</html>