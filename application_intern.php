<?php
session_start();

// Get user_id from GET or SESSION
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : ($_SESSION['user_id'] ?? 0);
if ($userId === 0) {
    die("User ID is missing or invalid.");
}

require_once 'db.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch applications
$stmt = $conn->prepare("
    SELECT 
        a.id,
        a.job_id,
        a.status,
        j.job_title,
        j.company_name AS company
    FROM applications a
    JOIN job_posts j ON a.job_id = j.id
    WHERE a.intern_id = ?
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("i", $userId);

if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}

$result = $stmt->get_result();
$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Applications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #d6e2ee;
            padding: 2rem;
        }

        h1 {
            margin-bottom: 2rem;
            text-align: center;
        }

        .table-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .top-bar {
            text-align: right;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="top-bar">
    <a href="dashboard_intern.php?user_id=<?= $userId ?>" class="btn btn-primary">Find Jobs</a>
</div>

<h1>My Applications</h1>

<div class="container table-container">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Application ID</th>
            <th>Job ID</th>
            <th>Job Title</th>
            <th>Company</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($applications as $app): ?>
            <tr>
                <td><?= htmlspecialchars($app['id']) ?></td>
                <td><?= htmlspecialchars($app['job_id']) ?></td>
                <td><?= htmlspecialchars($app['job_title']) ?></td>
                <td><?= htmlspecialchars($app['company']) ?></td>
                <td><?= htmlspecialchars($app['status']) ?></td>
                <td>
                    <a href="delete_application.php?id=<?= $app['id'] ?>&user_id=<?= $userId ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this application?')">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($applications)): ?>
            <tr>
                <td colspan="6" class="text-center">No applications found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
