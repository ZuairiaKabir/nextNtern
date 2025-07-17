<?php
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: application_intern.php?user_id=$userId");
exit;
