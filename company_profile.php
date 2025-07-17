<?php
require_once 'db.php';

if (!isset($_GET['id'])) {
    echo "No employer specified.";
    exit();
}

$employerUserId = intval($_GET['id']);  // This is the same as users.id and employers.user_id

$stmt = $conn->prepare("
    SELECT e.*, u.email 
    FROM employers e 
    JOIN users u ON e.user_id = u.id 
    WHERE e.user_id = ?
");
$stmt->bind_param("i", $employerUserId);
$stmt->execute();
$result = $stmt->get_result();
$employer = $result->fetch_assoc();

if (!$employer) {
    echo "Employer profile not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($employer['company_name']) ?> - Company Profile</title>
    <style>
        .cover-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
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
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        h1, p {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="cover-section">
        <img src="<?= htmlspecialchars($employer['cover_photo'] ?: 'images/default-cover.jpg') ?>" class="cover-photo" alt="Cover">
        <img src="<?= htmlspecialchars($employer['profile_picture'] ?: 'images/default-logo.png') ?>" class="profile-picture" alt="Logo">
    </div>

    <div class="company-details">
        <h1><?= htmlspecialchars($employer['company_name']) ?></h1>
        <p><strong>Email:</strong> <?= htmlspecialchars($employer['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($employer['phone']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($employer['address']) ?></p>
        <p><strong>Website:</strong> <a href="<?= htmlspecialchars($employer['website']) ?>" target="_blank"><?= htmlspecialchars($employer['website']) ?></a></p>
    </div>
</body>
</html>
