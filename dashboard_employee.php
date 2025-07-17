<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: Login.php");
    exit();
}
$userId = $_SESSION['user_id'];
require_once 'db.php';
$employerId = $_SESSION['user_id'];
// Check if employer profile is incomplete
$stmt = $conn->prepare("SELECT profile_picture, company_name FROM employers WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$empProf = $stmt->get_result()->fetch_assoc();
$stmt->close();

$showProfileAlert = false;
if (empty($empProf['company_name']) || empty($empProf['profile_picture'])) {
    $showProfileAlert = true;
}

$stmt = $conn->prepare("SELECT * FROM job_posts WHERE employer_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $employerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $jobPosts = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - nextNtern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }

        .dashboard-header {
            background: linear-gradient(to right, #1c1a85, #0f0e52);
            color: white;
            padding: 1rem 0;
            /* reduced height */
            margin-bottom: 2rem;
        }

        .job-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #1c1a85;
        }

        .job-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .job-title {
            color: #1c1a85;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .job-description {
            color: #555;
            margin-bottom: 0.75rem;
        }

        .skills-badge {
            background-color: #e3f1f6;
            color: #0f0e52;
            padding: 0.35em 0.65em;
            border-radius: 50rem;
            font-size: 0.75em;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            display: inline-block;
        }

        .btn-view-applicants {
            background-color: #1c1a85;
            color: white;
            padding: 0.4rem 1rem;
            border-radius: 5px;
            font-size: 0.9rem;
            margin-top: 1rem;
        }

        .btn-view-applicants:hover {
            background-color: #00b6c4;
            color: white;
        }

        .btn-delete-all {
            background-color: #dc3545;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            margin-top: 2rem;
        }

        .btn-delete-all:hover {
            background-color: #bb2d3b;
            color: white;
        }

        .applicant-count {
            background-color: #1c1a85;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            margin-left: 0.5rem;
        }

        form[role="search"] input {
            width: 180px;
        }
    </style>
</head>

<body>

    <!-- Custom Header -->
    <div style="background-color: #eef5fb; padding: 1rem 2rem;">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h3 style="margin: 0; font-weight: bold;">
                    next<span style="color: #1c1a85;">N</span>tern
                </h3>
                <p style="margin: 0; font-size: 0.9rem; color: #555;">Since 2025</p>
            </div>
        </div>
    </div>

    <!-- Navigation Bar with Search -->
    <nav style="background-color: #1c1a85; padding: 0.75rem 2rem;">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex gap-4">
                <a href="employer homepage.html" class="text-white fw-bold text-decoration-none">Home</a>
                <a href="employer profile.php" class="text-white fw-bold text-decoration-none">Profile Update</a>
            </div>
            <form class="d-flex" role="search">
                <input class="form-control form-control-sm me-2 search-input" type="search" placeholder="Search jobs..."
                    aria-label="Search">

                <button class="btn btn-outline-light btn-sm" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </nav>
<?php if (!empty($showProfileAlert)): ?>
  <div class="alert alert-warning text-center m-3">
    Please <a href="employer profile.php" class="alert-link">complete your profile</a> to unlock full features.
  </div>
<?php endif; ?>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-briefcase me-2"></i>Dashboard</h2>
                    <p class="mb-0">Welcome back, NextNtern</p>
                </div>
                <div class="d-flex gap-2">
  <a href="notifications.php" class="btn btn-outline-warning">
    <i class="fas fa-bell me-2"></i>Notifications
  </a>
  <a href="post_job.php" class="btn btn-light">
    <i class="fas fa-plus me-2"></i>Post New Job
  </a>
</div>

            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h4 class="mb-4">Your Job Postings</h4>

                <?php if (count($jobPosts) === 0): ?>
    <div class="alert alert-info">You have no job postings yet.</div>
<?php else: ?>
    <?php foreach ($jobPosts as $post): ?>
        <div class="job-card">
            <h5 class="job-title"><?= htmlspecialchars($post['job_title']) ?></h5>
            <p class="job-description"><?= nl2br(htmlspecialchars($post['description'])) ?></p>
            <div>
                <?php
                $skills = explode(',', $post['requirements']);
                foreach ($skills as $skill):
                    $skill = trim($skill);
                    if ($skill):
                ?>
                    <span class="skills-badge"><?= htmlspecialchars($skill) ?></span>
                <?php endif; endforeach; ?>
            </div>
           <a href="candidateList.php?job_id=<?= $post['id'] ?>" class="btn btn-outline-primary btn-view-applicants">
    <i class="fas fa-users me-2"></i> View Applications
    
</a>


        </div>
    <?php endforeach; ?>
<?php endif; ?>


                <!-- Delete All Button -->
                <div class="text-center mt-5">
                    <button class="btn btn-delete-all" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash-alt me-2"></i>Delete All Job Postings
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>all</strong> job postings?</p>
                    <p class="text-danger"><i class="fas fa-exclamation-circle me-2"></i>This action cannot be undone.
                        All applications will be lost.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete All</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap & JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // View Applications
        document.querySelectorAll('.btn-view-applicants').forEach(button => {
            button.addEventListener('click', function () {
                const jobTitle = this.closest('.job-card').querySelector('.job-title').textContent;
                //alert(`Redirecting to applications for: ${jobTitle}`);
            });
        });

        // Delete All
        // Delete All
document.getElementById('confirmDelete').addEventListener('click', function () {
    if (confirm("Are you absolutely sure you want to delete all job postings and related applications?")) {
        fetch('delete_all_jobs.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('All job postings have been deleted.');
                document.querySelectorAll('.job-card').forEach(card => card.remove());
                const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
                modal.hide();
            } else {
                alert('Failed to delete jobs: ' + (data.error || 'Unknown error'));
            }
        });
    }
});


        // 🔍 Job Search Functionality
        document.querySelector('.search-input').addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const jobCards = document.querySelectorAll('.job-card');

            jobCards.forEach(card => {
                const title = card.querySelector('.job-title').textContent.toLowerCase();
                const description = card.querySelector('.job-description').textContent.toLowerCase();
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        fetch('get_applicant_counts.php')
  .then(response => response.json())
  .then(counts => {
    document.querySelectorAll('.applicant-count').forEach(span => {
      const jobId = span.getAttribute('data-job-id');
      span.textContent = counts[jobId] || 0;
    });
  });
    </script>

</body>

</html>