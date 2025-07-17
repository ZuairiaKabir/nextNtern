<?php
require_once 'db.php';

$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

$sql = "
    SELECT 
        a.id AS application_id,
        u.first_name,
        a.cover_letter,
        a.resume_url,
        a.status,
        a.applied_at
    FROM applications a
    JOIN users u ON a.intern_id = u.id
    WHERE a.job_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $jobId);
$stmt->execute();
$result = $stmt->get_result();

$applicants = [];
while ($row = $result->fetch_assoc()) {
    $applicants[] = $row;
}

header('Content-Type: application/json');
echo json_encode($applicants);
