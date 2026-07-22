<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'db.php';
$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch skills
$skills = [];
$stmt_sk = $conn->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
$stmt_sk->bind_param("i", $user_id);
$stmt_sk->execute();
$res_sk = $stmt_sk->get_result();
while ($row = $res_sk->fetch_assoc()) { $skills[] = $row['skill_name']; }
$skills_str = implode(', ', $skills);

// Fetch projects
$projects = [];
$stmt_pr = $conn->prepare("SELECT * FROM projects WHERE user_id = ?");
$stmt_pr->bind_param("i", $user_id);
$stmt_pr->execute();
$res_pr = $stmt_pr->get_result();
while ($row = $res_pr->fetch_assoc()) { $projects[] = $row; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - DevAnalyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="particles"></div>

    <nav class="navbar navbar-expand-lg navbar-custom py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fa-solid fa-code gradient-text me-2"></i> DevAnalyzer
            </a>
            <div class="ms-auto d-flex align-items-center">
                <i class="fa-solid fa-moon me-4" id="theme-toggle" title="Toggle Dark/Light Mode"></i>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fa-solid fa-power-off me-1"></i> Logout</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8" data-aos="fade-up">
                <div class="glass-card p-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bold m-0">Edit <span class="gradient-text">Profile</span></h2>
                        <a href="dashboard.php" class="btn btn-sm btn-outline-secondary rounded-pill">Back to Dashboard</a>
                    </div>
                    
                    <form action="update_profile.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        
                        <!-- 1. Basic Details -->
                        <h4 class="gradient-text mb-3">1. Basic Details</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Full Name *</label>
                                <input type="text" class="form-control bg-transparent" id="name" name="name" required value="<?= htmlspecialchars($user['name']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address (Read-Only)</label>
                                <input type="email" class="form-control bg-transparent" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" class="form-control bg-transparent" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-semibold">Location</label>
                                <input type="text" class="form-control bg-transparent" id="location" name="location" value="<?= htmlspecialchars($user['location'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="profile_image" class="form-label fw-semibold">Update Profile Picture</label>
                                <input class="form-control bg-transparent" type="file" id="profile_image" name="profile_image" accept="image/*">
                                <small class="text-muted">Leave blank to keep existing image.</small>
                            </div>
                            <div class="col-md-6">
                                <label for="resume" class="form-label fw-semibold">Update Resume</label>
                                <input class="form-control bg-transparent" type="file" id="resume" name="resume" accept=".pdf,.doc,.docx">
                                <small class="text-muted">Leave blank to keep existing resume.</small>
                            </div>
                        </div>

                        <!-- 2. About Section -->
                        <h4 class="gradient-text mb-3 mt-5">2. About You</h4>
                        <div class="mb-3">
                            <label for="tagline" class="form-label fw-semibold">Professional Tagline</label>
                            <input type="text" class="form-control bg-transparent" id="tagline" name="tagline" value="<?= htmlspecialchars($user['tagline'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="career_objective" class="form-label fw-semibold">Career Objective</label>
                            <input type="text" class="form-control bg-transparent" id="career_objective" name="career_objective" value="<?= htmlspecialchars($user['career_objective'] ?? '') ?>">
                        </div>

                        <div class="mb-4">
                            <label for="bio" class="form-label fw-semibold">Bio / Description</label>
                            <textarea class="form-control bg-transparent" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>

                        <!-- 3. Links & Skills -->
                        <h4 class="gradient-text mb-3 mt-5">3. Links & Skills</h4>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="github" class="form-label fw-semibold">GitHub Profile URL *</label>
                                <input type="url" class="form-control bg-transparent" id="github" name="github" required value="<?= htmlspecialchars($user['github']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="linkedin" class="form-label fw-semibold">LinkedIn Profile URL</label>
                                <input type="url" class="form-control bg-transparent" id="linkedin" name="linkedin" value="<?= htmlspecialchars($user['linkedin'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="skills" class="form-label fw-semibold">Technical Skills (Comma-separated) *</label>
                            <input type="text" class="form-control bg-transparent" id="skills" name="skills" required value="<?= htmlspecialchars($skills_str) ?>">
                        </div>

                        <!-- 4. Projects -->
                        <h4 class="gradient-text mb-3 mt-5">4. Projects</h4>
                        <div id="projects-container">
                            <?php foreach($projects as $index => $project): ?>
                            <div class="project-entry border rounded p-4 mb-3 position-relative" style="border-color: var(--card-border) !important; background: rgba(255,255,255,0.02);">
                                <?php if($index > 0): ?>
                                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-2 remove-project" aria-label="Close"></button>
                                <?php endif; ?>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control bg-transparent" name="project_title[]" required value="<?= htmlspecialchars($project['project_title']) ?>" placeholder="Project Title *">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control bg-transparent" name="project_tech[]" value="<?= htmlspecialchars($project['technologies'] ?? '') ?>" placeholder="Technologies Used (e.g. HTML, CSS, JS)">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="url" class="form-control bg-transparent" name="project_github[]" value="<?= htmlspecialchars($project['github_link'] ?? '') ?>" placeholder="GitHub Repo Link">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="url" class="form-control bg-transparent" name="project_demo[]" value="<?= htmlspecialchars($project['live_demo'] ?? '') ?>" placeholder="Live Demo URL">
                                    </div>
                                </div>
                                <textarea class="form-control bg-transparent" name="project_desc[]" rows="2" required placeholder="Project Description *"><?= htmlspecialchars($project['description']) ?></textarea>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline-custom btn-sm mb-5" id="addProjectBtn">
                            <i class="fa-solid fa-plus"></i> Add Another Project
                        </button>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-gradient btn-lg">
                                <i class="fa-solid fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
