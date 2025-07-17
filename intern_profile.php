<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$saved = false;
// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_profile'])) {
    // Step 1: Fetch file paths before deletion
    $selectSql = "SELECT resume_url, profile_pic_url FROM intern_profiles WHERE user_id = ?";
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->bind_param("i", $userId);
    $selectStmt->execute();
    $res = $selectStmt->get_result();
    $resumeUrl = $profilePicUrl = '';

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $resumeUrl = $row['resume_url'];
        $profilePicUrl = $row['profile_pic_url'];
    }

    // Step 2: Delete from intern_profiles table
    $deleteSql = "DELETE FROM intern_profiles WHERE user_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    if (!$deleteStmt) die("Delete prepare failed: " . $conn->error);
    $deleteStmt->bind_param("i", $userId);

    if ($deleteStmt->execute()) {
        // Step 3: Delete files from filesystem
        if (!empty($resumeUrl)) {
            $cvPath = __DIR__ . '/' . $resumeUrl;
            if (file_exists($cvPath)) unlink($cvPath);
        }

        if (!empty($profilePicUrl)) {
            $picPath = __DIR__ . '/' . $profilePicUrl;
            if (file_exists($picPath)) unlink($picPath);
        }

        // Step 4: Also delete from users table
$deleteUserSql = "DELETE FROM users WHERE id = ?";
$deleteUserStmt = $conn->prepare($deleteUserSql);
if ($deleteUserStmt) {
    $deleteUserStmt->bind_param("i", $userId);
    $deleteUserStmt->execute();
    $deleteUserStmt->close();
}

// Step 5: Destroy session and redirect
session_destroy();
header("Location: index.html?deleted=1");
exit();

    } else {
        die("Delete failed: " . $deleteStmt->error);
    }
}


// Check if a profile already exists
$sql = "SELECT * FROM intern_profiles WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $profile = $result->fetch_assoc();
} else {
    // Create blank profile for the user
    $insertSql = "INSERT INTO intern_profiles (user_id) VALUES (?)";
    $insertStmt = $conn->prepare($insertSql);
    if (!$insertStmt) die("Insert prepare failed: " . $conn->error);
    $insertStmt->bind_param("i", $userId);
    $insertStmt->execute();

    // Blank profile to fill the form
    $profile = [
        'full_name' => '',
        'phone' => '',
        'skills' => '',
        'bio' => '',
        'resume_url' => '',
        'profile_pic_url' => ''
    ];
}

// Handle profile form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['info_phone'] ?? '';
    $skills = $_POST['skills'] ?? '';
    $bio = $_POST['experience'] ?? '';
    $resume_url = $profile['resume_url'];
    $profile_pic_url = $profile['profile_pic_url'];

    // Handle photo upload
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = __DIR__ . '/uploads/pics/';
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

        $fileName = time() . '_' . basename($_FILES['photo']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
            $profile_pic_url = 'uploads/pics/' . $fileName;
        }
    }
    if (isset($_POST['cvOption']) && $_POST['cvOption'] === 'upload' && !empty($_FILES['cv_file']['name'])) {
    $cvUploadDir = __DIR__ . '/uploads/cvs/';
    if (!file_exists($cvUploadDir)) mkdir($cvUploadDir, 0755, true);

    $cvFileName = time() . '_' . basename($_FILES['cv_file']['name']);
    $cvTargetPath = $cvUploadDir . $cvFileName;

    // Optional: validate file type for security
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (in_array($_FILES['cv_file']['type'], $allowedTypes)) {
        if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $cvTargetPath)) {
            $resume_url = 'uploads/cvs/' . $cvFileName;
        } else {
            // Handle upload failure if needed
        }
    } else {
        // Invalid file type
        die("Invalid CV file type. Please upload PDF or Word document.");
    }
}

   $updateSql = "UPDATE intern_profiles SET full_name=?, phone=?, skills=?, bio=?, resume_url=?, profile_pic_url=? WHERE user_id=?";
$updateStmt = $conn->prepare($updateSql);
if (!$updateStmt) die("Update prepare failed: " . $conn->error);

// Note: 6 strings and 1 integer = "ssssssi"
$updateStmt->bind_param("ssssssi", $full_name, $phone, $skills, $bio, $resume_url, $profile_pic_url, $userId);
$updateStmt->execute();


    // Update local profile variable
    $profile = [
        'full_name' => $full_name,
        'phone' => $phone,
        'skills' => $skills,
        'bio' => $bio,
        'resume_url' => $resume_url,
        'profile_pic_url' => $profile_pic_url
    ];

    $saved = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Intern Profile</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
        crossorigin="anonymous" />
    <style>
        body {
            background-color: #d6e2ee;
            padding: 2rem;
        }
        

        .profile-pic {
            max-width: 150px;
            border: 2px dashed #aaa;
            padding: 10px;
            display: block;
            margin: 0 auto 10px auto;
        }
        .editable {
            border-bottom: 1px dashed #999;
            padding: 2px 4px;
            min-width: 150px;
            display: inline-block;
        }
        .label-bold {
            font-weight: bold;
        }
        button {
            margin-top: 15px;
        }
        .current-cv p {
  font-size: 1rem;
}
.current-cv a {
  color: #007bff;
  text-decoration: underline;
}

    </style>
</head>
<body>
<div class="intern-container">
  <form method="POST" enctype="multipart/form-data" id="profileForm">
    <div class="photo-section">
      <img src="<?= htmlspecialchars($profile['profile_pic_url'] ?: 'images/default-placeholder.png') ?>" class="profile-pic" alt="Profile Photo" />
      <label for="photo" class="label-bold">Upload Photo:</label><br />
      <input type="file" id="photo" name="photo" accept="image/*" />
    </div>

    <div class="info-section editable-box">
      <p><span class="label-bold">Name:</span> 
         <span contenteditable="true" id="full_name_edit" class="editable"><?= htmlspecialchars($profile['full_name']) ?></span>
      </p>
      <p><span class="label-bold">Phone:</span> 
         <span contenteditable="true" id="phone_edit" class="editable"><?= htmlspecialchars($profile['phone']) ?></span>
      </p>
      <p><span class="label-bold">Skills:</span><br />
         <span contenteditable="true" id="skills_edit" class="editable" style="min-height:50px; display:block;"><?= htmlspecialchars($profile['skills']) ?></span>
      </p>
      <p><span class="label-bold">Experience:</span><br />
         <span contenteditable="true" id="bio_edit" class="editable" style="min-height:50px; display:block;"><?= htmlspecialchars($profile['bio']) ?></span>
      </p>
      <div class="cv-section mt-3">
  <label class="label-bold">CV:</label><br/>

  <input type="radio" id="uploadCvOption" name="cvOption" value="upload" checked />
  <label for="uploadCvOption">Upload CV</label>

  <input type="radio" id="createCvOption" name="cvOption" value="create" style="margin-left: 20px;" />
  <label for="createCvOption">Create CV</label>
</div>

<div id="uploadCvDiv" style="margin-top: 10px;">
  <input type="file" name="cv_file" accept=".pdf,.doc,.docx" />
  <div class="current-cv mt-3">
  <?php if (!empty($profile['resume_url'])): ?>
    <p><strong>Current CV:</strong> <a href="<?= htmlspecialchars($profile['resume_url']) ?>" target="_blank" rel="noopener noreferrer">View CV</a></p>
  <?php else: ?>
    <p><em>No CV uploaded yet.</em></p>
  <?php endif; ?>
</div>

</div>


      <!-- Hidden inputs to hold contenteditable values before submit -->
      <input type="hidden" name="full_name" id="full_name" />
      <input type="hidden" name="info_phone" id="info_phone" />
      <input type="hidden" name="skills" id="skills" />
      <input type="hidden" name="experience" id="experience" />

      <button type="submit">Save</button>
    </div>
  </form>
  <?php if ($saved): ?>
    <script>alert('Profile updated successfully');</script>
  <?php endif; ?>

  <!-- Apply Button -->
  <div class="apply-section mt-4">
      <a href="application_intern.php" class="btn btn-apply btn-primary">Applications</a>
  </div>

  <!-- Delete & Back Buttons -->
  <div class="bottom-buttons mt-2">
      <form method="POST" onsubmit="return confirm('Are you sure you want to delete your profile? This action cannot be undone.');" style="display:inline;">
  <input type="hidden" name="delete_profile" value="1" />
  <button type="submit" class="btn btn-danger">Delete</button>
</form>

      <a href="dashboard_intern.php" class="btn btn-secondary btn-back">Back to Dashboard</a>
  </div>
</div>

<script>
  // Before submitting, copy contenteditable text into hidden inputs
  document.getElementById('profileForm').addEventListener('submit', function(e) {
    if (createCvOption.checked) {
      e.preventDefault();
      window.location.href = 'cvMaker.php?user_id=<?= $userId ?>';
      return;
    }
    document.getElementById('full_name').value = document.getElementById('full_name_edit').innerText.trim();
    document.getElementById('info_phone').value = document.getElementById('phone_edit').innerText.trim();
    document.getElementById('skills').value = document.getElementById('skills_edit').innerText.trim();
    document.getElementById('experience').value = document.getElementById('bio_edit').innerText.trim();
  });
  const uploadCvOption = document.getElementById('uploadCvOption');
const createCvOption = document.getElementById('createCvOption');
const uploadCvDiv = document.getElementById('uploadCvDiv');

uploadCvOption.addEventListener('change', () => {
  uploadCvDiv.style.display = 'block';
});

createCvOption.addEventListener('change', () => {
  if (createCvOption.checked) {
    window.location.href = 'cvMaker.php?user_id=<?= $userId ?>';
  }
});

</script>


</body>
</html>