<?php
require_once 'db.php';

if (!isset($_GET['user_id'])) {
    die("No profile specified.");
}

$userId = (int)$_GET['user_id'];

$stmt = $conn->prepare("SELECT * FROM intern_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die("Profile not found.");
}

$profile = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intern Profile - View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f0f2f5;
            padding: 2rem;
        }
        .profile-pic {
            max-width: 150px;
            border: 2px solid #ccc;
            padding: 5px;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            padding: 2rem;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <h3 class="mb-4">Intern Profile</h3>
        <div class="text-center mb-4">
            <img src="<?= htmlspecialchars($profile['profile_pic_url'] ?: 'images/default-placeholder.png') ?>" class="profile-pic" alt="Profile Picture">
        </div>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($profile['full_name']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($profile['phone']) ?></p>
        <p><strong>Skills:</strong> <?= nl2br(htmlspecialchars($profile['skills'])) ?></p>
        <p><strong>Experience:</strong> <?= nl2br(htmlspecialchars($profile['bio'])) ?></p>
        <p><strong>Resume:</strong> 
            <?php if (!empty($profile['resume_url'])): ?>
                <a href="<?= htmlspecialchars($profile['resume_url']) ?>" target="_blank">View CV</a>
            <?php else: ?>
                <em>No resume uploaded.</em>
            <?php endif; ?>
        </p>
    </div>
</body>
</html>
