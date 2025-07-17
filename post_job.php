<?php
session_start();
require_once 'db.php'; // your database connection

function sanitize($conn, $data) {
    return $conn->real_escape_string(trim($data));
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and collect POST data
    $companyName = sanitize($conn, $_POST['companyName'] ?? '');
    $companyWebsite = sanitize($conn, $_POST['companyWebsite'] ?? '');
    $companyDescription = sanitize($conn, $_POST['companyDescription'] ?? '');
    $jobTitle = sanitize($conn, $_POST['jobTitle'] ?? '');
    $jobType = sanitize($conn, $_POST['jobType'] ?? '');
    $duration = sanitize($conn, $_POST['duration'] ?? '');
    $stipend = sanitize($conn, $_POST['stipend'] ?? '');
    $location = sanitize($conn, $_POST['location'] ?? '');
    $jobDescription = sanitize($conn, $_POST['jobDescription'] ?? '');
    $requirements = sanitize($conn, $_POST['requirements'] ?? '');
    $benefits = sanitize($conn, $_POST['benefits'] ?? '');
    $deadline = isset($_POST['deadline']) ? sanitize($conn, $_POST['deadline']) : null;
    $startDate = isset($_POST['startDate']) ? sanitize($conn, $_POST['startDate']) : null;
    $applicationMethod = sanitize($conn, $_POST['applicationMethod'] ?? '');
    $contactName = sanitize($conn, $_POST['contactName'] ?? '');
    $contactEmail = sanitize($conn, $_POST['contactEmail'] ?? '');
    $contactPhone = sanitize($conn, $_POST['contactPhone'] ?? '');

    $stmt = $conn->prepare("INSERT INTO job_posts 
        (employer_id, company_name, company_website, company_description, job_title, job_type, duration, stipend, location, description, requirements, benefits, application_deadline, start_date, application_method, contact_name, contact_email, contact_phone, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "isssssssssssssssss",
        $employer_id, $companyName, $companyWebsite, $companyDescription, $jobTitle, $jobType,
        $duration, $stipend, $location, $jobDescription, $requirements, $benefits,
        $deadline, $startDate, $applicationMethod, $contactName, $contactEmail, $contactPhone
    );

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: dashboard_employee.php?message=job_posted");
        exit();
    } else {
        $error = "Error posting job: " . $stmt->error;
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Post Job - nextNtern</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }

    .form-container {
      max-width: 800px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    .form-header {
      color: #1c1a85;
      border-bottom: 2px solid #1c1a85;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
    }

    .btn-custom {
      background-color: #1c1a85;
      color: white;
      border: none;
    }

    .btn-custom:hover {
      background-color: #00b6c4;
      color: white;
    }

    .required:after {
      content: " *";
      color: red;
    }
  </style>
</head>

<body>
  <div class="container py-5">
    <div class="form-container">
      <h2 class="form-header"><i class="fas fa-briefcase me-2"></i>Post a New Internship</h2>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form id="jobPostForm" action="post_job.php" method="POST">
        <!-- Company Information -->
        <div class="mb-4">
          <h5 class="mb-3 text-primary"><i class="fas fa-building me-2"></i>Company Information</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="companyName" class="form-label required">Company Name</label>
              <input type="text" class="form-control" id="companyName" name="companyName" required
                value="<?= isset($_POST['companyName']) ? htmlspecialchars($_POST['companyName']) : '' ?>" />
            </div>
            <div class="col-md-6">
              <label for="companyWebsite" class="form-label">Website</label>
              <input type="url" class="form-control" id="companyWebsite" name="companyWebsite" placeholder="https://"
                value="<?= isset($_POST['companyWebsite']) ? htmlspecialchars($_POST['companyWebsite']) : '' ?>" />
            </div>
            <div class="col-12">
              <label for="companyDescription" class="form-label">Brief Description</label>
              <textarea class="form-control" id="companyDescription" name="companyDescription" rows="2"><?= isset($_POST['companyDescription']) ? htmlspecialchars($_POST['companyDescription']) : '' ?></textarea>
            </div>
          </div>
        </div>

        <!-- Position Details -->
        <div class="mb-4">
          <h5 class="mb-3 text-primary"><i class="fas fa-info-circle me-2"></i>Position Details</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="jobTitle" class="form-label required">Internship Title</label>
              <input type="text" class="form-control" id="jobTitle" name="jobTitle" required
                value="<?= isset($_POST['jobTitle']) ? htmlspecialchars($_POST['jobTitle']) : '' ?>" />
            </div>
            <div class="col-md-6">
              <label for="jobType" class="form-label required">Type</label>
              <select class="form-select" id="jobType" name="jobType" required>
                <option value="">Select...</option>
                <option value="Full-time" <?= (isset($_POST['jobType']) && $_POST['jobType'] === 'Full-time') ? 'selected' : '' ?>>Full-time</option>
                <option value="Part-time" <?= (isset($_POST['jobType']) && $_POST['jobType'] === 'Part-time') ? 'selected' : '' ?>>Part-time</option>
                <option value="Remote" <?= (isset($_POST['jobType']) && $_POST['jobType'] === 'Remote') ? 'selected' : '' ?>>Remote</option>
                <option value="Hybrid" <?= (isset($_POST['jobType']) && $_POST['jobType'] === 'Hybrid') ? 'selected' : '' ?>>Hybrid</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="duration" class="form-label required">Duration</label>
              <select class="form-select" id="duration" name="duration" required>
                <option value="">Select...</option>
                <option value="1-3 months" <?= (isset($_POST['duration']) && $_POST['duration'] === '1-3 months') ? 'selected' : '' ?>>1-3 months</option>
                <option value="3-6 months" <?= (isset($_POST['duration']) && $_POST['duration'] === '3-6 months') ? 'selected' : '' ?>>3-6 months</option>
                <option value="6-12 months" <?= (isset($_POST['duration']) && $_POST['duration'] === '6-12 months') ? 'selected' : '' ?>>6-12 months</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="stipend" class="form-label">Stipend</label>
              <input type="text" class="form-control" id="stipend" name="stipend" placeholder="e.g. $500/month"
                value="<?= isset($_POST['stipend']) ? htmlspecialchars($_POST['stipend']) : '' ?>" />
            </div>
            <div class="col-md-4">
              <label for="location" class="form-label required">Location</label>
              <input type="text" class="form-control" id="location" name="location" required
                value="<?= isset($_POST['location']) ? htmlspecialchars($_POST['location']) : '' ?>" />
            </div>
          </div>
        </div>

        <!-- Job Description -->
        <div class="mb-4">
          <h5 class="mb-3 text-primary"><i class="fas fa-file-alt me-2"></i>Job Description</h5>
          <div class="mb-3">
            <label for="jobDescription" class="form-label required">Responsibilities</label>
            <textarea class="form-control" id="jobDescription" name="jobDescription" rows="4" required><?= isset($_POST['jobDescription']) ? htmlspecialchars($_POST['jobDescription']) : '' ?></textarea>
          </div>
          <div class="mb-3">
            <label for="requirements" class="form-label required">Requirements</label>
            <textarea class="form-control" id="requirements" name="requirements" rows="3" required><?= isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : '' ?></textarea>
            <div class="form-text">List required skills, qualifications, or experience</div>
          </div>
          <div class="mb-3">
            <label for="benefits" class="form-label">Benefits</label>
            <textarea class="form-control" id="benefits" name="benefits" rows="2"><?= isset($_POST['benefits']) ? htmlspecialchars($_POST['benefits']) : '' ?></textarea>
          </div>
        </div>

        <!-- Application Process -->
        <div class="mb-4">
          <h5 class="mb-3 text-primary"><i class="fas fa-clipboard-list me-2"></i>Application Process</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="deadline" class="form-label">Application Deadline</label>
              <input type="date" class="form-control" id="deadline" name="deadline"
                value="<?= isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : '' ?>" />
            </div>
            <div class="col-md-6">
              <label for="startDate" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="startDate" name="startDate"
                value="<?= isset($_POST['startDate']) ? htmlspecialchars($_POST['startDate']) : '' ?>" />
            </div>
            <div class="col-12">
              <label for="applicationMethod" class="form-label">How to Apply</label>
              <textarea class="form-control" id="applicationMethod" name="applicationMethod" rows="2"
                placeholder="Any special instructions for applicants"><?= isset($_POST['applicationMethod']) ? htmlspecialchars($_POST['applicationMethod']) : '' ?></textarea>
            </div>
          </div>
        </div>

        <!-- Contact Information -->
        <div class="mb-4">
          <h5 class="mb-3 text-primary"><i class="fas fa-envelope me-2"></i>Contact Information</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="contactName" class="form-label required">Contact Person</label>
              <input type="text" class="form-control" id="contactName" name="contactName" required
                value="<?= isset($_POST['contactName']) ? htmlspecialchars($_POST['contactName']) : '' ?>" />
            </div>
            <div class="col-md-6">
              <label for="contactEmail" class="form-label required">Email</label>
              <input type="email" class="form-control" id="contactEmail" name="contactEmail" required
                value="<?= isset($_POST['contactEmail']) ? htmlspecialchars($_POST['contactEmail']) : '' ?>" />
            </div>
            <div class="col-md-6">
              <label for="contactPhone" class="form-label">Phone</label>
              <input type="tel" class="form-control" id="contactPhone" name="contactPhone"
                value="<?= isset($_POST['contactPhone']) ? htmlspecialchars($_POST['contactPhone']) : '' ?>" />
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
          
          <button type="submit" class="btn btn-custom px-4">
            <i class="fas fa-paper-plane me-2"></i>Post Internship
          </button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
