<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["first_name"];
    $lastName = $_POST["last_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    // Encrypt the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Turn off default MySQLi exception throwing
    mysqli_report(MYSQLI_REPORT_OFF);

    try {
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
        } else {
            if ($stmt->errno == 1062) {
                // Duplicate email error
                echo "<script>alert('This email is already registered. Please use a different one.'); window.history.back();</script>";
            } else {
                // Other SQL error
                echo "<script>alert('Registration failed. Please try again.'); window.history.back();</script>";
            }
        }

        $stmt->close();
        $conn->close();

    } catch (Exception $e) {
        echo "<script>alert('An unexpected error occurred. Please try again.'); window.history.back();</script>";
    }
}
?>
