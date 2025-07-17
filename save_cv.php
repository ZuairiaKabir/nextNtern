<?php
session_start();
require_once 'db.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "User not logged in.";
    exit;
}

$intern_id = $_SESSION['user_id'];

// Check if file was uploaded
if (!isset($_FILES['cv_file'])) {
    http_response_code(400);
    echo "No file uploaded.";
    exit;
}

$file = $_FILES['cv_file'];

// Check for errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo "Upload error: " . $file['error'];
    exit;
}

// Validate file type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mime !== 'application/pdf') {
    http_response_code(400);
    echo "Invalid file type. Only PDF files are allowed.";
    exit;
}

// Set upload directory
$upload_dir = 'C:/xampp/htdocs/nextNtern/uploads/cvs/';

// Create directory if needed
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Sanitize filename
$filename = basename($file['name']);
$file_path = $upload_dir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    http_response_code(500);
    echo "Failed to save file.";
    exit;
}

// Update database with relative path
$relative_path = 'uploads/cvs/' . $filename;

$stmt = $conn->prepare("UPDATE intern_profiles SET resume_url = ? WHERE user_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo "Prepare statement failed: " . $conn->error;
    exit;
}

$stmt->bind_param('si', $relative_path, $intern_id);

if (!$stmt->execute()) {
    http_response_code(500);
    echo "Execute failed: " . $stmt->error;
    exit;
}

$stmt->close();
echo "CV saved successfully!";
?>