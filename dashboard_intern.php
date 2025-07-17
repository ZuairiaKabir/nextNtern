<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: Login.php");
    exit();
}

$userId = $_SESSION['user_id'];


// Database connection details
$servername = "localhost";
$username = "root";  // your DB username
$password = "";      // your DB password
$dbname = "nextntern_db"; // your DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// After session and DB code:
$stmt = $conn->prepare("SELECT profile_pic_url, full_name FROM intern_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$prof = $stmt->get_result()->fetch_assoc();
if (empty($prof['full_name']) || empty($prof['profile_pic_url'])) {
    $showProfileAlert = true;
}

// Fetch jobs from database
$sql = "SELECT id, employer_id, job_title, company_name, location, duration FROM job_posts ORDER BY id DESC";

$result = $conn->query($sql);

// Prepare jobs array
$jobs = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f8f9fa;
      scroll-behavior: smooth;
    }

    /* Updated Header Styles */
    .top-header {
      background: linear-gradient(to right, #edf4ff, #e3f1f6);
      padding: 1rem 0;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .logo-text {
      font-size: 1.8rem;
      font-weight: bold;
      color: #333;
      transition: transform 0.3s ease;
    }

    .logo-text:hover {
      transform: scale(1.05);
    }

    .logo-highlight {
      color: #1c1a85;
      font-weight: bold;
    }

    .subtext {
      font-size: 0.75rem;
      color: #555;
    }

    .main-nav {
      background-color: #1c1a85;
      padding: 0.3rem 0;
    }

    .main-nav .nav-links a {
      color: white !important;
      font-weight: 500;
      transition: all 0.3s ease-in-out;
      padding: 8px 12px !important;
      text-decoration: none;
    }

    .main-nav .nav-links a:hover {
      background-color: #2c2c6d;
      border-radius: 4px;
    }

    .employers-link {
      color: #ffc107 !important;
      font-weight: bold;
      position: relative;
      text-decoration: none;
    }

    .employers-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 0;
      background-color: #ffc107;
      transition: width 0.3s ease;
    }

    .employers-link:hover::after {
      width: 100%;
    }

    /* Rest of your existing styles */
    .dashboard {
      max-width: 900px;
      margin: 40px auto;
      background: white;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }

    .dashboard h2 {
      text-align: center;
      color: #1c1a85;
      margin-bottom: 30px;
    }

    .job-card {
      background-color: #eef1f6;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .job-card h4 {
      color: #1c1a85;
    }

    .job-card .btn {
      margin: 5px;
    }
  </style>
</head>

<body>

  <!-- Updated Header Start -->
  <header class="animate__animated animate__fadeInDown">
    <div class="top-header">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <div class="d-flex flex-column">
            <div class="brand">
              <span class="logo-text">next<span class="logo-highlight">N</span>tern</span>
            </div>
            <small class="subtext">Since 2025</small>
          </div>
        </div>
      </div>
    </div>

    <div class="main-nav">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center">
          <div class="nav-links d-flex">
            <a href="index.html">Home</a>
              <a href="application_intern.php?user_id=<?= $_SESSION['user_id'] ?>">Find Jobs</a>
            <a href="application_form.php">Post Applications</a>
            <a href="intern_profile.php">Profile Update</a>
          </div>
          <div class="d-flex align-items-center gap-3">
  <a href="notifications_intern.php" class="btn btn-sm btn-warning text-dark fw-bold">
    <i class="fas fa-bell"></i> Notifications
  </a>
  <a href="employer homepage.html" class="employers-link">Employers →</a>
</div>

        </div>
      </div>
    </div>
  </header>
  <!-- Updated Header End -->

  <div class="dashboard">
    <?php if (!empty($showProfileAlert)): ?>
  <div class="alert alert-warning text-center">
    Please <a href="intern_profile.php">complete your profile</a> to use all features.
  </div>
<?php endif; ?>

    <h2>Dashboard</h2>
    <p>Welcome back,
      <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!
    </p>
    <div class="row mb-4 text-center">
      <div class="col-md-3 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Find Jobs</h5>
            <p class="card-text">Explore available internship opportunities.</p>
            <a href="application_intern.php" class="btn btn-primary">Go</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Post Applications</h5>
            <p class="card-text">Submit your applications directly.</p>
            <a href="application_form.php" class="btn btn-primary">Go</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Profile Update</h5>
            <p class="card-text">Keep your profile up to date for better matches.</p>
            <a href="intern_profile.php" class="btn btn-primary">Go</a>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title">Employers</h5>
            <p class="card-text">Post job offers and browse candidates.</p>
            <a href="employer homepage.html" class="btn btn-warning text-dark">Go</a>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
      <a href="intern_profile.php" class="btn btn-outline-primary">Profile</a>
      
    </div>

    <div id="job-list">
      <!-- Job cards will be dynamically inserted here -->
    </div>

    <nav>
      <ul class="pagination justify-content-center">
        <li class="page-item"><a class="page-link" href="#" onclick="prevPage()">Previous</a></li>
        <li class="page-item"><a class="page-link" href="#" onclick="gotoPage(1)">1</a></li>
        <li class="page-item"><a class="page-link" href="#" onclick="gotoPage(2)">2</a></li>
        <li class="page-item"><a class="page-link" href="#" onclick="gotoPage(3)">3</a></li>
        <li class="page-item"><a class="page-link" href="#" onclick="nextPage()">Next</a></li>
      </ul>
    </nav>
  </div>
<script>
   const urlParams = new URLSearchParams(window.location.search);
  const userId = urlParams.get('user_id');

  if (userId) {
    document.querySelector('a[href="application_intern.php"]').href = `application_intern.php?user_id=${userId}`;
    document.querySelector('a[href="application_form.php"]').href = `application_form.php?user_id=${userId}`;
    document.querySelector('a[href="intern_profile.php"]').href = `intern_profile.php?user_id=${userId}`;
  }
  // Use PHP jobs array converted to JS
  const jobs = <?php echo json_encode($jobs); ?>;

  let currentPage = 1;
  const jobsPerPage = 3;

  function displayJobs() {
    const jobList = document.getElementById("job-list");
    jobList.innerHTML = "";
    const start = (currentPage - 1) * jobsPerPage;
    const end = start + jobsPerPage;
    const paginatedJobs = jobs.slice(start, end);

    paginatedJobs.forEach(job => {
      const card = document.createElement("div");
      card.className = "job-card";
      card.innerHTML = `
        <h4>${job.job_title}</h4>
        <p>${job.company_name}<br>Location: ${job.location}<br>Duration: ${job.duration}</p>
        <div>
          <button class="btn btn-outline-primary" onclick="window.location.href='company_profile.php?id=${job.employer_id}'">Company Profile</button>

          <button class="btn btn-primary" onclick="window.location.href='application_form.php?job_id=${job.id}'">Application</button>

        </div>
      `;
      jobList.appendChild(card);
    });
  }

  function gotoPage(page) {
    currentPage = page;
    displayJobs();
  }

  function nextPage() {
    if (currentPage * jobsPerPage < jobs.length) {
      currentPage++;
      displayJobs();
    }
  }

  function prevPage() {
    if (currentPage > 1) {
      currentPage--;
      displayJobs();
    }
  }

  document.addEventListener("DOMContentLoaded", displayJobs);
</script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>