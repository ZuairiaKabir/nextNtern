<!-- forgot-password.php -->
<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';

    if ($email && $newPassword) {
        // Check if user exists
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result && $result->num_rows === 1) {
            // User found, update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password_hash = ? WHERE email = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ss", $newPasswordHash, $email);

            if ($updateStmt->execute()) {
                $success = "Password updated successfully!";
            } else {
                $error = "Error updating password.";
            }
        } else {
            $error = "Email not found.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password | nextNtern</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="col-md-6 offset-md-3">
    <h3 class="text-center text-primary mb-4">Reset Password</h3>

    <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" action="forgot-password.php">
      <div class="mb-3">
        <label for="email" class="form-label">Registered Email</label>
        <input type="email" name="email" id="email" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="newPassword" class="form-label">New Password</label>
        <input type="password" name="newPassword" id="newPassword" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
      <div class="text-center mt-3">
        <a href="Login.php">Back to login</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
