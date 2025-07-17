<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($job_id === 0) {
        die("Invalid or missing job ID.");
    }

    // Validate required fields
    if (!isset($_POST['cover_letter']) || !isset($_FILES['cv'])) {
        die("Incomplete form submission.");
    }

    // DB connection
    $conn = new mysqli("localhost", "root", "", "nextntern_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $cover_letter = $conn->real_escape_string(trim($_POST['cover_letter']));
    $user_id = $_SESSION['user_id'];

    // Handle CV upload
    $uploadDir = "uploads/cvs/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $cv = $_FILES['cv'];
    $cvName = basename($cv['name']);
    $cvTmpName = $cv['tmp_name'];
    $cvError = $cv['error'];
    $fileExt = strtolower(pathinfo($cvName, PATHINFO_EXTENSION));

    if ($cvError !== 0 || $fileExt !== 'pdf') {
        die("Upload error. Only PDF files are allowed.");
    }

    $newFileName = uniqid('cv_', true) . '.' . $fileExt;
    $uploadFilePath = $uploadDir . $newFileName;

    if (!move_uploaded_file($cvTmpName, $uploadFilePath)) {
        die("Failed to upload CV file.");
    }

    // Insert application using prepared statement
    $stmt = $conn->prepare("INSERT INTO applications (intern_id, job_id, resume_url, cover_letter) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $job_id, $uploadFilePath, $cover_letter);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo "<div style='text-align:center; margin-top:30px;'>
                <h3>✅ Application submitted successfully!</h3>
                <a href='dashboard_intern.php' class='btn btn-primary mt-3'>Back to Dashboard</a>
              </div>";
        exit();
    } else {
        echo "Error submitting application: " . $stmt->error;
        $stmt->close();
        $conn->close();
    }
}

}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Application Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #d6e2ee;
            padding: 2rem;
        }

        .form-container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 850px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>

<body>

    <div class="form-container">
        <h1>Application Form</h1>

        <form id="applicationForm" action="" method="POST" enctype="multipart/form-data">
        
    <div class="form-group">
    <label for="cover_letter">Cover Letter / Motivation</label>
    <textarea class="form-control" id="cover_letter" name="cover_letter" rows="6" required></textarea>
</div>

<div class="form-group">
    <label for="cv">Upload Your CV (PDF only)</label>
    <input type="file" class="form-control-file" id="cv" name="cv" accept=".pdf" required>
</div>


    <div class="text-center mt-4">
        <button type="submit" class="btn btn-success">Submit Application</button>
        <button type="button" class="btn btn-secondary ml-2" onclick="goBack()">Back</button>
    </div>

</form>

    </div>

    <script>
        function goBack() {
            window.history.back(); // goes back to previous page
        }

        /*document.getElementById("applicationForm").addEventListener("submit", function (event) {
            event.preventDefault();

            const q1 = document.getElementById("q1").value.trim();
            const q2 = document.getElementById("q2").value.trim();
            const q3 = document.getElementById("q3").value.trim();
            const q4 = document.getElementById("q4").value.trim();
            const salary = document.getElementById("salary").value.trim();
            const cv = document.getElementById("cv").files[0];

            if (!cv || !cv.name.endsWith(".pdf")) {
                alert("Please upload a valid PDF file for your CV.");
                return;
            }

            alert("Application submitted!\n\n" +
                "Q1: " + q1 + "\n" +
                "Q2: " + q2 + "\n" +
                "Q3: " + q3 + "\n" +
                "Q4: " + q4 + "\n" +
                "Expected Salary: $" + salary);
        });*/
    </script>

</body>

</html>