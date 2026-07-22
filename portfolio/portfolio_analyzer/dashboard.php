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

// Determine static portfolio URL
$name_parts = explode(' ', trim($user['name']));
$clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name_parts[0]));
if (empty($clean_name)) { $clean_name = "user" . $user_id; }
$static_portfolio_url = 'user_portfolios/' . $clean_name . '.html';

// Generate it if it doesn't exist yet (for older users)
if (!file_exists(__DIR__ . '/' . $static_portfolio_url)) {
    require_once 'generate_static.php';
    generate_static_portfolio($user_id, $conn);
}

// Fetch score history
$history = [];
$stmt_hist = $conn->prepare("SELECT * FROM analysis_history WHERE user_id = ? ORDER BY created_at DESC");
$stmt_hist->bind_param("i", $user_id);
$stmt_hist->execute();
$res_hist = $stmt_hist->get_result();
while ($row = $res_hist->fetch_assoc()) {
    $history[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - DevAnalyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="particles"></div>

    <div class="loader-container" id="loader">
        <div class="spinner mb-3"></div>
        <h4 class="fw-bold gradient-text">Analyzing your profile...</h4>
        <p class="text-muted">Extracting GitHub data and evaluating skills.</p>
    </div>

    <nav class="navbar navbar-expand-lg navbar-custom py-3 sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fa-solid fa-code gradient-text me-2"></i> DevAnalyzer
            </a>
            <div class="ms-auto d-flex align-items-center">
                <i class="fa-solid fa-moon me-4" id="theme-toggle" title="Toggle Dark/Light Mode"></i>
                <a href="logout.php" class="btn btn-outline-danger btn-sm rounded-pill"><i class="fa-solid fa-power-off me-1"></i> Logout</a>
            </div>
        </div>
    </nav>

    <main class="container my-5">
        <div class="row" data-aos="fade-up">
            <!-- Left Panel: Actions -->
            <div class="col-lg-4 mb-4">
                <div class="glass-card text-center h-100">
                    <?php if (!empty($user['profile_image'])): ?>
                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" class="rounded-circle mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid var(--primary-color);">
                    <?php else: ?>
                        <div class="bg-gradient-custom text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 100px; height: 100px; font-size: 2.5rem;">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <h3 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h3>
                    <p class="text-muted mb-4"><?= htmlspecialchars($user['email']) ?></p>

                    <div class="d-grid gap-3">
                        <a href="<?= htmlspecialchars($static_portfolio_url) ?>" class="btn btn-outline-custom" target="_blank">
                            <i class="fa-solid fa-eye me-2"></i> View Portfolio
                        </a>
                        <a href="edit_profile.php" class="btn btn-outline-custom">
                            <i class="fa-solid fa-pen-to-square me-2"></i> Edit Profile
                        </a>
                        <button class="btn btn-gradient" id="analyzeBtn">
                            <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Analyze Again
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Panel: History -->
            <div class="col-lg-8 mb-4">
                <div class="glass-card h-100">
                    <h4 class="fw-bold mb-4 border-bottom pb-2" style="border-color: var(--card-border) !important;">
                        <i class="fa-solid fa-chart-line text-primary me-2"></i> Score History
                    </h4>
                    
                    <?php if (count($history) > 0): ?>
                        <div class="table-responsive">
                            <table class="table text-main bg-transparent align-middle">
                                <thead>
                                    <tr>
                                        <th class="text-muted border-0">Date & Time</th>
                                        <th class="text-muted border-0 text-center">Score</th>
                                        <th class="text-muted border-0">Status</th>
                                        <th class="text-muted border-0 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $index => $record): ?>
                                    <tr style="border-bottom: 1px solid var(--card-border);">
                                        <td class="py-3"><?= date('M d, Y h:i A', strtotime($record['created_at'])) ?></td>
                                        <td class="py-3 text-center">
                                            <span class="badge bg-<?= $record['score'] >= 80 ? 'success' : ($record['score'] >= 50 ? 'warning' : 'danger') ?> fs-6 rounded-pill">
                                                <?= $record['score'] ?>/100
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <?php if ($index == 0): ?>
                                                <span class="badge bg-primary rounded-pill small">Latest</span>
                                            <?php else: ?>
                                                <span class="text-muted small">Archived</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 text-end">
                                            <a href="result.html?history_id=<?= $record['id'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fa-solid fa-inbox fs-1 text-muted opacity-50 mb-3"></i>
                            <p class="text-muted">You haven't analyzed your portfolio yet.</p>
                            <button class="btn btn-gradient btn-sm mt-2" onclick="document.getElementById('analyzeBtn').click();">Run First Analysis</button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/main.js"></script>
    <script>
        document.getElementById('analyzeBtn').addEventListener('click', function() {
            document.getElementById('loader').style.display = 'flex';
            fetch('analyze.php')
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        window.location.href = 'result.html?history_id=' + data.history_id;
                    } else {
                        alert('Analysis failed: ' + data.message);
                        document.getElementById('loader').style.display = 'none';
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred during analysis.');
                    document.getElementById('loader').style.display = 'none';
                });
        });
    </script>
</body>
</html>
