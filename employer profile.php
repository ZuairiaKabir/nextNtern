<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$saved = false;

// Check if employer profile exists
$sql = "SELECT * FROM employers WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $employer = $result->fetch_assoc();
} else {
    // Create a blank employer profile
    $insertSql = "INSERT INTO employers (user_id, company_name, address, phone, website, cover_photo, profile_picture) VALUES (?, '', '', '', '', '', '')";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) die("Insert prepare failed: " . $conn->error);
    $insertStmt->bind_param("i", $userId);
    $insertStmt->execute();

    $employer = [
        'company_name' => '',
        'address' => '',
        'phone' => '',
        'website' => '',
        'cover_photo' => '',
        'profile_picture' => ''
    ];
}

// Fetch email from users table
$stmt2 = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$userData = $stmt2->get_result()->fetch_assoc();
$stmt2->close();
$email = $userData ? $userData['email'] : '';

// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = $_POST['company_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $website = $_POST['website'] ?? '';

    $cover_photo = $employer['cover_photo'];
    $profile_picture = $employer['profile_picture'];

    // Upload cover photo
    if (!empty($_FILES['cover_photo']['name'])) {
        $uploadDir = __DIR__ . '/uploads/pics/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = time() . '_' . basename($_FILES['cover_photo']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $targetPath)) {
            $cover_photo = 'uploads/pics/' . $fileName;

        }
    }

    // Upload profile picture
    if (!empty($_FILES['profile_picture']['name'])) {
       $uploadDir = __DIR__ . '/uploads/logos/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = time() . '_' . basename($_FILES['profile_picture']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetPath)) {
           $profile_picture = 'uploads/logos/' . $fileName;
        }
    }

    // Update employer profile
    $stmt = $conn->prepare("UPDATE employers SET company_name = ?, address = ?, phone = ?, website = ?, cover_photo = ?, profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("ssssssi", $companyName, $address, $phone, $website, $cover_photo, $profile_picture, $userId);
    $stmt->execute();
    $stmt->close();

    // Update email in users table
    $stmt2 = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt2->bind_param("si", $email, $userId);
    $stmt2->execute();
    $stmt2->close();

    $saved = true;
    header("Location: employer profile.php?updated=1");
    exit();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($employer['company_name']) ?> - Profile</title>
  <style>
    .cover-photo {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
}

.profile-picture {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 3px solid white;
  margin-top: -60px;
  background-color: white;
  object-fit: cover;
}

  .company-details {
    max-width: 600px;
    margin: 20px auto;
    background: #f9f9f9;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 0 10px #ccc;
  }

  .company-details input[type="text"],
  .company-details input[type="email"],
  .company-details input[type="url"] {
    padding: 10px;
    margin-top: 5px;
    margin-bottom: 15px;
    width: 100%;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 16px;
  }

  .company-details .input-large {
    font-size: 24px;
    font-weight: bold;
    padding: 12px;
  }

  .company-details button {
    padding: 12px 20px;
    font-size: 16px;
    background-color: #007bff;
    border: none;
    border-radius: 6px;
    color: white;
    cursor: pointer;
    margin-top: 10px;
  }

  .company-details button:hover {
    background-color: #0056b3;
  }
</style>

  <link rel="stylesheet" href="style.css">
</head>
<body class="EmployerBody">
<div class="fullcover">
  <div class="cover-section">
    <img src="<?= htmlspecialchars($employer['cover_photo'] ?: 'images/default-cover.jpg') ?>" alt="Cover" class="cover-photo" />
<img src="<?= htmlspecialchars($employer['profile_picture'] ?: 'images/default-logo.png') ?>" alt="Company Logo" class="profile-picture" />

  </div>

  <div class="company-details">
 
  <form method="POST" action="employer profile.php" enctype="multipart/form-data">
<p><strong>Cover Photo:</strong><br>
  <input type="file" name="cover_photo" accept="image/*">
</p>

<p><strong>Company Logo:</strong><br>
  <input type="file" name="profile_picture" accept="image/*">
</p>

    <p><strong>Company name:</strong><br>
     <h1 class="company-name">
    <input type="text" name="company_name" value="<?= htmlspecialchars($employer['company_name']) ?>" required style="font-size:1.5em; width:100%;">
  </h1></p>
    <p><strong>Email:</strong><br>
      <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required style="width:100%;">
    </p>
    <p><strong>Address:</strong><br>
      <input type="text" name="address" value="<?= htmlspecialchars($employer['address']) ?>" style="width:100%;">
    </p>
    <p><strong>Phone:</strong><br>
      <input type="text" name="phone" value="<?= htmlspecialchars($employer['phone']) ?>" style="width:100%;">
    </p>
    <p><strong>Website:</strong><br>
      <input type="url" name="website" value="<?= htmlspecialchars($employer['website']) ?>" style="width:100%;">
    </p>
    <button type="submit" name="update_profile">Update</button>
  </form>
</div>


  

<div class="faq-section">
  <h2>Frequently Asked Questions</h2>
  <div class="faq">
    <h4>What is the application process?</h4>
    <p>Submit your resume and await further communication from HR.</p>
  </div>
  <div class="faq">
    <h4>Can I apply for multiple roles?</h4>
    <p>Yes, feel free to apply for all suitable positions.</p>
  </div>
</div>

<footer class="footer">
  <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($employer['company_name']) ?>. All rights reserved.</p>
</footer>
</body>
</html> 