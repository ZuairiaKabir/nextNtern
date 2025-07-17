<?php
require 'db_connection.php';

$sql = "SELECT job_id, COUNT(*) AS count FROM applications GROUP BY job_id";
$result = $conn->query($sql);

$counts = [];
while ($row = $result->fetch_assoc()) {
    $counts[$row['job_id']] = $row['count'];
}

echo json_encode($counts);
?>
