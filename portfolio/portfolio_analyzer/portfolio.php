<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id == 0 && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}
if ($user_id == 0) die("Invalid User ID");

// Detect if we are being included by generate_static.php
$is_generating = isset($_GET['is_generating']) ? true : false;

if (!$is_generating) {
    // If accessed directly, redirect to the static portfolio
    $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user_res = $stmt_user->get_result()->fetch_assoc();
    
    if ($user_res) {
        $name_parts = explode(' ', trim($user_res['name']));
        $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name_parts[0]));
        if (empty($clean_name)) { $clean_name = "user" . $user_id; }
        $static_url = 'user_portfolios/' . $clean_name . '.html';
        
        // Only redirect if the file actually exists
        if (!file_exists(__DIR__ . '/' . $static_url)) {
            require_once 'generate_static.php';
            generate_static_portfolio($user_id, $conn);
        }
        
        if (file_exists(__DIR__ . '/' . $static_url)) {
            if (!headers_sent()) {
                header("Location: " . $static_url);
            } else {
                echo '<script>window.location.href="' . $static_url . '";</script>';
            }
            exit();
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) die("User not found");

$skills = [];
$stmt_sk = $conn->prepare("SELECT skill_name FROM skills WHERE user_id = ?");
$stmt_sk->bind_param("i", $user_id);
$stmt_sk->execute();
$res_sk = $stmt_sk->get_result();
while ($row = $res_sk->fetch_assoc()) { $skills[] = $row['skill_name']; }

$projects = [];
$stmt_pr = $conn->prepare("SELECT * FROM projects WHERE user_id = ?");
$stmt_pr->bind_param("i", $user_id);
$stmt_pr->execute();
$res_pr = $stmt_pr->get_result();
while ($row = $res_pr->fetch_assoc()) { $projects[] = $row; }

$github_username = basename(parse_url($user['github'], PHP_URL_PATH));
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['name']) ?> | Developer Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom py-3 sticky-top no-print">
        <div class="container">
            <a class="navbar-brand fs-4" href="#"><?= explode(' ', trim(htmlspecialchars($user['name'])))[0] ?><span class="gradient-text">.dev</span></a>
            
            <div class="d-flex align-items-center order-lg-last">
                <i class="fa-solid fa-moon me-3" id="theme-toggle" title="Toggle Dark/Light Mode"></i>
                <button class="btn btn-outline-custom ms-2" onclick="copyPortfolioLink()"><i class="fa-solid fa-link"></i> Copy Link</button>
                <button class="btn btn-gradient ms-2" onclick="downloadPDF()"><i class="fa-solid fa-file-pdf"></i> PDF</button>
                <button class="navbar-toggler border-0 ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <i class="fa-solid fa-bars text-main"></i>
                </button>
            </div>

            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="#hero">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#skills">Skills</a></li>
                    <li class="nav-item"><a class="nav-link" href="#projects">Projects</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="particles no-print"></div>

    <div id="portfolio-content">
        
        <!-- 1. Hero Section -->
        <section id="hero" class="hero-section py-5">
            <div class="container py-5 text-center text-lg-start">
                <div class="row align-items-center">
                    <div class="col-lg-7 mb-5 mb-lg-0 order-2 order-lg-1" data-aos="fade-right">
                        
                        <div class="d-flex flex-wrap gap-3 mb-3 justify-content-center justify-content-lg-start">
                            <?php if(!empty($user['location'])): ?>
                                <span class="badge contact-badge rounded-pill px-3 py-2"><i class="fa-solid fa-location-dot text-primary me-2"></i> <?= htmlspecialchars($user['location']) ?></span>
                            <?php endif; ?>
                            <?php if(!empty($user['phone'])): ?>
                                <span class="badge contact-badge rounded-pill px-3 py-2"><i class="fa-solid fa-phone text-success me-2"></i> <?= htmlspecialchars($user['phone']) ?></span>
                            <?php endif; ?>
                        </div>

                        <h1 class="hero-title mb-2">Hi, I'm <span class="gradient-text"><?= htmlspecialchars($user['name']) ?></span></h1>
                        <h3 class="hero-subtitle text-muted"><?= htmlspecialchars(!empty($user['tagline']) ? $user['tagline'] : 'Software Engineer & Digital Creator') ?></h3>
                        
                        <div class="mb-4" style="max-width: 650px;">
                            <p class="text-muted fs-5 mb-3"><?= nl2br(htmlspecialchars(!empty($user['bio']) ? $user['bio'] : 'Passionate developer building scalable web applications.')) ?></p>
                            <?php if(!empty($user['career_objective'])): ?>
                                <div class="p-3 border-start border-primary border-4 rounded bg-light bg-opacity-10 text-muted fst-italic">
                                    "<?= htmlspecialchars($user['career_objective']) ?>"
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start no-print">
                            <a href="#projects" class="btn btn-gradient">View My Work</a>
                            <?php if(!empty($user['resume_path'])): ?>
                                <a href="<?= htmlspecialchars($user['resume_path']) ?>" target="_blank" class="btn btn-outline-custom">Download CV</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-5 text-center order-1 order-lg-2 mb-5 mb-lg-0" data-aos="fade-left">
                        <?php if (!empty($user['profile_image'])): ?>
                            <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="<?= htmlspecialchars($user['name']) ?>" class="hero-avatar shadow-lg" style="width: 250px; height: 250px; border-width: 8px;">
                        <?php else: ?>
                            <div class="bg-gradient-custom text-white rounded-circle d-inline-flex align-items-center justify-content-center hero-avatar shadow-lg" style="width: 250px; height: 250px; font-size: 6rem; border-width: 8px;">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. Skills Section -->
        <section id="skills" class="py-5" style="background: rgba(0,0,0,0.02);">
            <div class="container py-5">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title gradient-text display-6 fw-bold">My Expertise</h2>
                    <p class="text-muted">Technologies I've been working with recently</p>
                </div>
                
                <div class="glass-card p-5" data-aos="zoom-in">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <?php foreach($skills as $skill): ?>
                            <div class="skill-badge">
                                <?= htmlspecialchars($skill) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. Projects Showcase -->
        <section id="projects" class="py-5">
            <div class="container py-5">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title gradient-text display-6 fw-bold">Featured Projects</h2>
                    <p class="text-muted">Some of the real-world applications I've built</p>
                </div>
                
                <div class="row g-5">
                    <?php foreach($projects as $index => $project): ?>
                        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?= ($index % 2) * 100 ?>">
                            <div class="glass-card project-card p-0 shadow-sm border-0 d-flex flex-column">
                                <div class="project-img-placeholder">
                                    <i class="fa-solid fa-laptop-code text-white" style="font-size: 4rem; opacity: 0.5;"></i>
                                </div>
                                <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                    <h4 class="fw-bold mb-2"><?= htmlspecialchars($project['project_title']) ?></h4>
                                    
                                    <?php if(!empty($project['technologies'])): ?>
                                    <div class="mb-4">
                                        <?php 
                                        $techs = explode(',', $project['technologies']);
                                        foreach($techs as $tech): 
                                            if(!empty(trim($tech))):
                                        ?>
                                            <span class="tech-tag"><?= htmlspecialchars(trim($tech)) ?></span>
                                        <?php endif; endforeach; ?>
                                    </div>
                                    <?php endif; ?>

                                    <p class="text-muted mb-4 fs-6 lh-lg flex-grow-1"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                                    
                                    <div class="mt-auto no-print d-flex gap-3">
                                        <?php if(!empty($project['github_link'])): ?>
                                            <a href="<?= htmlspecialchars($project['github_link']) ?>" target="_blank" class="btn btn-outline-secondary rounded-pill btn-sm"><i class="fa-brands fa-github me-1"></i> Code</a>
                                        <?php endif; ?>
                                        <?php if(!empty($project['live_demo'])): ?>
                                            <a href="<?= htmlspecialchars($project['live_demo']) ?>" target="_blank" class="btn btn-gradient btn-sm"><i class="fa-solid fa-external-link-alt me-1"></i> Live Demo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- 8. Contact Section -->
        <section id="contact" class="py-5" style="background: rgba(0,0,0,0.02);">
            <div class="container py-5 text-center">
                <div data-aos="fade-up">
                    <h2 class="display-5 fw-bold mb-4">Let's <span class="gradient-text">Connect</span></h2>
                    <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 600px;">I'm currently looking for new opportunities. Whether you have a question or just want to say hi, I'll try my best to get back to you!</p>
                </div>
                
                <div class="d-flex justify-content-center gap-4 flex-wrap" data-aos="zoom-in">
                    <a href="mailto:<?= $user['email'] ?>" class="glass-card text-decoration-none px-4 py-3 d-flex align-items-center gap-3 contact-link">
                        <i class="fa-solid fa-envelope fs-2 text-danger"></i>
                        <span class="fw-semibold text-main fs-5">Email Me</span>
                    </a>
                    
                    <?php if(!empty($user['linkedin'])): 
                        $linkedin_url = $user['linkedin'];
                        if (!preg_match("~^(?:f|ht)tps?://~i", $linkedin_url)) {
                            $linkedin_url = "https://" . $linkedin_url;
                        }
                    ?>
                    <a href="<?= htmlspecialchars($linkedin_url) ?>" target="_blank" class="glass-card text-decoration-none px-4 py-3 d-flex align-items-center gap-3 contact-link">
                        <i class="fa-brands fa-linkedin fs-2 text-primary"></i>
                        <span class="fw-semibold text-main fs-5">LinkedIn</span>
                    </a>
                    <?php endif; ?>

                    <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank" class="glass-card text-decoration-none px-4 py-3 d-flex align-items-center gap-3 contact-link">
                        <i class="fa-brands fa-github fs-2 text-main"></i>
                        <span class="fw-semibold text-main fs-5">GitHub</span>
                    </a>
                </div>
            </div>
        </section>

    </div> <!-- END PORTFOLIO CONTENT -->

    <!-- 5. GitHub Open Source Stats -->
    <section id="github-stats" class="py-5">
        <div class="container py-5">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="section-title gradient-text display-6 fw-bold">Open Source Contributions</h2>
                <p class="text-muted">My activity on GitHub</p>
            </div>
            
            <div class="row g-4 justify-content-center" id="github-stats-container">
                <div class="col-12 text-center text-muted">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p>Loading GitHub stats...</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 text-center mt-5 no-print" style="border-top: 1px solid var(--card-border);">
        <div class="container">
            <p class="mb-0 fw-semibold text-muted">&copy; <?= date('Y') ?> <?= htmlspecialchars($user['name']) ?>. Built with DevAnalyzer.</p>
        </div>
    </footer>

    <!-- HIDDEN PDF TEMPLATE (Colorized & Professional) -->
    <div id="pdf-template" style="display: none; width: 750px; padding: 50px; background: #ffffff; color: #1a1a1a; font-family: 'Poppins', Arial, sans-serif; margin: 0 auto;">
        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 3px solid #4A90E2; padding-bottom: 25px; margin-bottom: 40px;">
            <div>
                <h1 style="margin: 0; color: #0f172a; font-size: 36px; font-weight: 800;"><?= htmlspecialchars($user['name']) ?></h1>
                <p style="margin: 8px 0; color: #6C5CE7; font-size: 20px; font-weight: 700;"><?= htmlspecialchars($user['tagline']) ?></p>
                <div style="margin-top: 10px; color: #475569; font-size: 14px; font-weight: 500;">
                    <span style="margin-right: 15px;"><i class="fa-solid fa-envelope" style="color: #4A90E2;"></i> <?= htmlspecialchars($user['email']) ?></span>
                    <span style="margin-right: 15px;"><i class="fa-solid fa-phone" style="color: #4A90E2;"></i> <?= htmlspecialchars($user['phone']) ?></span>
                    <span><i class="fa-solid fa-location-dot" style="color: #4A90E2;"></i> <?= htmlspecialchars($user['location']) ?></span>
                </div>
            </div>
            <?php if (!empty($user['profile_image'])): ?>
                <img src="<?= htmlspecialchars($user['profile_image']) ?>" style="width: 120px; height: 120px; border-radius: 20px; border: 4px solid #4A90E2; object-fit: cover;">
            <?php endif; ?>
        </div>

        <div style="margin-bottom: 40px;">
            <h3 style="color: #ffffff; background: linear-gradient(135deg, #4A90E2, #6C5CE7); padding: 10px 20px; border-radius: 8px; font-size: 18px; margin-bottom: 15px;">ABOUT ME</h3>
            <p style="line-height: 1.8; color: #334155; font-size: 15px;"><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
        </div>

        <div style="margin-bottom: 40px;">
            <h3 style="color: #ffffff; background: linear-gradient(135deg, #4A90E2, #6C5CE7); padding: 10px 20px; border-radius: 8px; font-size: 18px; margin-bottom: 15px;">TECHNICAL SKILLS</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <?php foreach($skills as $skill): ?>
                    <span style="background: rgba(74, 144, 226, 0.1); border: 1px solid rgba(74, 144, 226, 0.2); padding: 6px 15px; border-radius: 6px; font-size: 14px; color: #4A90E2; font-weight: 600;"><?= htmlspecialchars($skill) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div>
            <h3 style="color: #ffffff; background: linear-gradient(135deg, #4A90E2, #6C5CE7); padding: 10px 20px; border-radius: 8px; font-size: 18px; margin-bottom: 15px;">FEATURED PROJECTS</h3>
            <?php foreach($projects as $project): ?>
                <div style="margin-bottom: 30px; page-break-inside: avoid; border-left: 4px solid #00C9A7; padding-left: 20px;">
                    <h4 style="margin: 0 0 5px 0; color: #0f172a; font-size: 18px;"><?= htmlspecialchars($project['project_title']) ?></h4>
                    <p style="margin: 0 0 10px 0; font-size: 13px; color: #6C5CE7; font-weight: 700; letter-spacing: 1px;"><?= strtoupper(htmlspecialchars($project['technologies'])) ?></p>
                    <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.6;"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap & Plugins JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Fetch GitHub Stats directly from client-side to ensure static deployment compatibility
        document.addEventListener("DOMContentLoaded", function() {
            const githubUsername = '<?= htmlspecialchars($github_username) ?>';
            if (githubUsername) {
                fetch('https://api.github.com/users/' + encodeURIComponent(githubUsername))
                    .then(response => {
                        if (!response.ok) throw new Error('API Rate limit or user not found');
                        return response.json();
                    })
                    .then(data => {
                        document.getElementById('github-stats-container').innerHTML = `
                            <div class="col-md-4" data-aos="fade-up">
                                <div class="glass-card text-center h-100 border-0 shadow-sm p-4">
                                    <i class="fa-brands fa-github fs-1 text-main mb-3"></i>
                                    <h3 class="fw-bold">${data.public_repos || 0}</h3>
                                    <p class="text-muted mb-0">Public Repositories</p>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                                <div class="glass-card text-center h-100 border-0 shadow-sm p-4">
                                    <i class="fa-solid fa-users fs-1 text-primary mb-3"></i>
                                    <h3 class="fw-bold">${data.followers || 0}</h3>
                                    <p class="text-muted mb-0">Followers</p>
                                </div>
                            </div>
                            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                                <div class="glass-card text-center h-100 border-0 shadow-sm p-4">
                                    <i class="fa-solid fa-code-branch fs-1 text-success mb-3"></i>
                                    <h3 class="fw-bold">${data.public_gists || 0}</h3>
                                    <p class="text-muted mb-0">Public Gists</p>
                                </div>
                            </div>
                        `;
                    })
                    .catch(err => {
                        document.getElementById('github-stats-container').innerHTML = 
                            '<div class="col-12 text-center text-muted"><p><i class="fa-brands fa-github fs-4 mb-2"></i><br>View my projects directly on <a href="<?= htmlspecialchars($user['github']) ?>" target="_blank">GitHub</a></p></div>';
                    });
            } else {
                document.getElementById('github-stats-container').innerHTML = '<div class="col-12 text-center text-muted"><p>GitHub profile not linked.</p></div>';
            }
        });

        function copyPortfolioLink() {
            const el = document.createElement('textarea');
            el.value = window.location.href;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('Portfolio link copied to clipboard!');
        }

        function downloadPDF() {
            const element = document.getElementById('pdf-template');
            
            // Temporarily show the template for capture
            element.style.display = 'block';
            
            const opt = {
                margin:       10,
                filename:     '<?= htmlspecialchars($user['name']) ?>_Portfolio.pdf',
                image:        { type: 'jpeg', quality: 1.0 },
                html2canvas:  { 
                    scale: 3, 
                    useCORS: true, 
                    letterRendering: true
                },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // Capture the clean template instead of the complex website
            html2pdf().set(opt).from(element).save().then(() => {
                element.style.display = 'none'; // Hide it back
            });
        }
    </script>
</body>
</html>
