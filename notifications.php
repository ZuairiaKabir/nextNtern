<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: Login.php");
    exit();
}
require_once 'db.php';

$employerId = $_SESSION['user_id'];

// Get job applications for this employer’s jobs
$sql = "
    SELECT 
        j.job_title,
        i.full_name,
        i.user_id AS intern_id,
        i.profile_pic_url,
        a.applied_at,
        a.status
    FROM applications a
    JOIN job_posts j ON a.job_id = j.id
    JOIN intern_profiles i ON a.intern_id = i.user_id
    WHERE j.employer_id = ?
    ORDER BY a.applied_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employerId);
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Notifications - nextNtern</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container my-5">
    <h2><i class="fas fa-bell"></i> Application Notifications</h2>
    <?php if (empty($applications)): ?>
      <div class="alert alert-info mt-4">No applications yet.</div>
    <?php else: ?>
      <ul class="list-group mt-4">
        <?php foreach ($applications as $app): ?>
          <li class="list-group-item d-flex align-items-center">
            <img src="<?= htmlspecialchars($app['profile_pic_url'] ?: 'images/default-user.jpg') ?>" class="rounded-circle me-3" width="50" height="50">
            <div class="flex-grow-1">
              <strong><?= htmlspecialchars($app['full_name']) ?></strong> applied to <em><?= htmlspecialchars($app['job_title']) ?></em><br>
              <small class="text-muted"><?= date('M d, Y h:i A', strtotime($app['applied_at'])) ?></small>
            </div>
            <a href="view_intern_profile.php?user_id=<?= $app['intern_id'] ?>" class="btn btn-outline-primary btn-sm">View Profile</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</body>
</html>
