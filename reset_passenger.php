<?php
require_once 'db_connect.php';

$email = 'passenger.test@example.com';
$new_password = 'Passenger@123';

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "UPDATE users SET password = ? WHERE email = ? AND role = 'passenger'"
);

$stmt->bind_param("ss", $hashed_password, $email);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "Passenger password reset successfully!";
} else {
    echo "No passenger account was updated. Check the email and role.";
}

$stmt->close();
$conn->close();
?>