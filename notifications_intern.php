<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.php");
    exit();
}

require_once 'db.php';

$internId = $_SESSION['user_id'];

$sql = "
    SELECT 
        j.job_title,
        e.company_name,
        a.status,
        a.updated_at
    FROM applications a
    JOIN job_posts j ON a.job_id = j.id
    JOIN employers e ON j.employer_id = e.user_id
    WHERE a.intern_id = ?
    ORDER BY a.updated_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $internId);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Notifications - nextNtern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h2><i class="fas fa-bell me-2"></i> Application Status Updates</h2>

        <?php if (empty($notifications)): ?>
            <div class="alert alert-info mt-4">No updates yet on your applications.</div>
        <?php else: ?>
            <ul class="list-group mt-4">
                <?php foreach ($notifications as $note): ?>
                    <li class="list-group-item">
                        Your application for <strong><?= htmlspecialchars($note['job_title']) ?></strong> at <strong><?= htmlspecialchars($note['company_name']) ?></strong> was <span class="badge bg-info text-dark"><?= htmlspecialchars($note['status']) ?></span> <br>
                        <small class="text-muted">Updated on <?= date('M d, Y h:i A', strtotime($note['updated_at'])) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
