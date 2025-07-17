<?php
require_once 'db.php';

$job_title = '';
$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

if ($jobId > 0) {
    $stmt = $conn->prepare("SELECT job_title FROM job_posts WHERE id = ?");
    $stmt->bind_param("i", $jobId);
    $stmt->execute();
    $stmt->bind_result($job_title);
    $stmt->fetch();
    $stmt->close();
}

// Handle POST request to update application status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['status'])) {
    $appId = (int)$_POST['application_id'];
    $newStatus = $_POST['status'];
    $validStatuses = ['pending', 'accepted', 'rejected'];

    if (in_array($newStatus, $validStatuses)) {
        $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $appId);
        if ($stmt->execute()) {
            header("Location: candidateList.php?job_id=$jobId&status_updated=1");
            exit;
        } else {
            echo "<div class='alert alert-danger'>Failed to update status: {$stmt->error}</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Invalid status value provided.</div>";
    }
}

// Fetch applicants for normal page load
$sql = "
    SELECT 
           a.id AS id,
    i.user_id AS user_id,
        i.full_name AS full_name,
        i.profile_pic_url,
        a.cover_letter,
        a.resume_url,
        a.status,
        a.applied_at
    FROM applications a
    JOIN intern_profiles i ON a.intern_id = i.user_id
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
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Applicants - nextNtern</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
    }
    .header {
      background: linear-gradient(to right, #edf4ff, #e3f1f6);
      padding: 20px 0;
      margin-bottom: 30px;
    }
    .applicants-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    .applicant-card {
      transition: all 0.3s ease;
    }
    .applicant-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .search-box, .filter-options {
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <div class="header">
    <div class="container d-flex justify-content-between align-items-center">
      <div>
        <h3><i class="fas fa-users"></i> Applicants for: <?= htmlspecialchars($job_title ?: 'Unknown Job') ?></h3>
        <p class="text-muted">NextNtern</p>
      </div>
      <div>
        <a href="dashboard_employee.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Jobs</a>
      </div>
    </div>
  </div>

  <div class="applicants-container">
    <?php if (isset($_GET['status_updated'])): ?>
      <div class="alert alert-success">Application status updated successfully!</div>
    <?php endif; ?>

    <div class="row mb-3">
      <div class="col-md-6">
        <input type="text" id="searchInput" class="form-control" placeholder="Search applicants...">
      </div>
      <div class="col-md-6">
        <select id="filterSelect" class="form-select">
          <option value="">Filter by status</option>
          <option value="pending">Pending</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <div id="applicantList">
      <?php foreach ($applicants as $app): ?>
        <div class="card mb-3 applicant-card" data-name="<?= strtolower($app['full_name']) ?>" data-status="<?= strtolower($app['status']) ?>">
          <div class="card-body d-flex align-items-center">
            <img src="<?= htmlspecialchars($app['profile_pic_url'] ?: 'images/default-user.jpg') ?>" alt="Photo" class="rounded-circle me-3" width="60" height="60">
            <div class="flex-grow-1">
              <h5 class="applicant-name mb-1"><?= htmlspecialchars($app['full_name']) ?></h5>
<p class="mb-2">Profile: <a href="view_intern_profile.php?user_id=<?= $app['user_id'] ?>" target="_blank">View Profile</a>
</p>

              <p><strong>Cover Letter:</strong> <?= nl2br(htmlspecialchars($app['cover_letter'] ?: 'N/A')) ?></p>
              <p><strong>Resume:</strong> <a href="<?= htmlspecialchars($app['resume_url']) ?>" target="_blank">View CV</a></p>
              <p><strong>Applied on:</strong> <?= date('M j, Y', strtotime($app['applied_at'])) ?></p>
              <p><strong>Status:</strong> <span class="badge bg-info text-dark"><?= htmlspecialchars($app['status']) ?></span></p>
            </div>
            <div>
              <form method="POST" onsubmit="return confirm('Accept this applicant?');" style="display:inline-block">
                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                <input type="hidden" name="status" value="accepted">
                <button class="btn btn-success">Accept</button>
              </form>
              <form method="POST" onsubmit="return confirm('Reject this applicant?');" style="display:inline-block">
                <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                <input type="hidden" name="status" value="rejected">
                <button class="btn btn-danger">Reject</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const applicantCards = document.querySelectorAll('.applicant-card');

    function applyFilters() {
      const searchTerm = searchInput.value.toLowerCase();
      const filterValue = filterSelect.value;

      applicantCards.forEach(card => {
        const name = card.getAttribute('data-name');
        const status = card.getAttribute('data-status');
        const matchesSearch = name.includes(searchTerm);
        const matchesFilter = !filterValue || status === filterValue;

        card.style.display = matchesSearch && matchesFilter ? '' : 'none';
      });
    }

    searchInput.addEventListener('input', applyFilters);
    filterSelect.addEventListener('change', applyFilters);
  </script>
</body>
</html>
