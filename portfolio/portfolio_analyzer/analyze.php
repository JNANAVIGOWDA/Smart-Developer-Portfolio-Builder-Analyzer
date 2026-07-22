<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized. Please log in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch skills count
$stmt_skills = $conn->prepare("SELECT COUNT(*) as count FROM skills WHERE user_id = ?");
$stmt_skills->bind_param("i", $user_id);
$stmt_skills->execute();
$skills_count = $stmt_skills->get_result()->fetch_assoc()['count'];

// Fetch projects count
$stmt_proj = $conn->prepare("SELECT COUNT(*) as count FROM projects WHERE user_id = ?");
$stmt_proj->bind_param("i", $user_id);
$stmt_proj->execute();
$projects_count = $stmt_proj->get_result()->fetch_assoc()['count'];

// Extract GitHub Username
$github_username = basename(parse_url($user['github'], PHP_URL_PATH));

// Fetch GitHub data internally
$url = "https://api.github.com/users/" . urlencode($github_username);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Smart-Portfolio-Analyzer');
$github_response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$github_repos = 0;
if ($httpcode == 200) {
    $github_data = json_decode($github_response, true);
    $github_repos = isset($github_data['public_repos']) ? intval($github_data['public_repos']) : 0;
}

// SCORING LOGIC
$score = 0;
$suggestions = [];

// Skills Score
if ($skills_count < 5) {
    $score += 10;
    $suggestions[] = "Learn more technologies like React, Node.js, or Python to broaden your skillset.";
} else if ($skills_count >= 5 && $skills_count <= 8) {
    $score += 20;
    $suggestions[] = "Good skillset! Consider mastering one specialized framework to stand out.";
} else {
    $score += 30;
    $suggestions[] = "Excellent variety of skills! Ensure you have deep knowledge in your core stack.";
}

// Projects Score
if ($projects_count < 2) {
    $score += 10;
    $suggestions[] = "Add more real-world projects to demonstrate your practical abilities.";
} else if ($projects_count >= 2 && $projects_count <= 4) {
    $score += 20;
    $suggestions[] = "Solid project portfolio. Make sure they are well-documented and deployed.";
} else {
    $score += 30;
    $suggestions[] = "Great project experience! Try contributing to open-source to further boost your profile.";
}

// GitHub Score
if ($github_repos < 5) {
    $score += 10;
    $suggestions[] = "Improve GitHub activity. Try pushing code more often and creating more repositories.";
} else if ($github_repos >= 5 && $github_repos <= 10) {
    $score += 15;
    $suggestions[] = "Decent GitHub presence. Aim to contribute consistently and write good READMEs.";
} else {
    $score += 20;
    $suggestions[] = "Awesome GitHub activity! You have a strong public coding record.";
}

// Resume Score
if (!empty($user['resume_path'])) {
    $score += 20;
} else {
    $suggestions[] = "Upload a professional resume (PDF/DOC) to provide a quick summary to recruiters.";
}

// Save Analysis to analysis_history table
$suggestions_json = json_encode($suggestions);
$stmt_analysis = $conn->prepare("INSERT INTO analysis_history (user_id, score, suggestions) VALUES (?, ?, ?)");
$stmt_analysis->bind_param("iis", $user_id, $score, $suggestions_json);

if ($stmt_analysis->execute()) {
    $history_id = $stmt_analysis->insert_id;
    echo json_encode(["status" => "success", "score" => $score, "history_id" => $history_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to save analysis history."]);
}
?>
