<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$employerId = $_SESSION['user_id'];

// Delete applications for this employer's jobs
$conn->query("
    DELETE a FROM applications a
    INNER JOIN job_posts j ON a.job_id = j.id
    WHERE j.employer_id = $employerId
");

// Delete employer's job posts
$stmt = $conn->prepare("DELETE FROM job_posts WHERE employer_id = ?");
$stmt->bind_param("i", $employerId);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
?>
