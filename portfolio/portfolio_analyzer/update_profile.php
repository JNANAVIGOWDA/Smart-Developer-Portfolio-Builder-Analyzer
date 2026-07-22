<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'db.php';
$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $github = $conn->real_escape_string($_POST['github']);
    $tagline = isset($_POST['tagline']) ? $conn->real_escape_string($_POST['tagline']) : '';
    $bio = isset($_POST['bio']) ? $conn->real_escape_string($_POST['bio']) : '';
    $phone = isset($_POST['phone']) ? $conn->real_escape_string($_POST['phone']) : '';
    $location = isset($_POST['location']) ? $conn->real_escape_string($_POST['location']) : '';
    $career_objective = isset($_POST['career_objective']) ? $conn->real_escape_string($_POST['career_objective']) : '';
    $linkedin = isset($_POST['linkedin']) ? $conn->real_escape_string($_POST['linkedin']) : '';
    
    $skills_raw = $_POST['skills'];
    $project_titles = $_POST['project_title'];
    $project_descs = $_POST['project_desc'];
    $project_techs = isset($_POST['project_tech']) ? $_POST['project_tech'] : [];
    $project_githubs = isset($_POST['project_github']) ? $_POST['project_github'] : [];
    $project_demos = isset($_POST['project_demo']) ? $_POST['project_demo'] : [];

    // Handle Resume Upload
    $resume_sql = "";
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['resume']['tmp_name'];
        $file_name = time() . '_res_' . basename($_FILES['resume']['name']);
        $file_dest = $upload_dir . $file_name;
        $allowed = ['pdf', 'doc', 'docx'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && move_uploaded_file($file_tmp, $file_dest)) {
            $resume_sql = ", resume_path = '" . $conn->real_escape_string($file_dest) . "'";
        }
    }

    // Handle Profile Image Upload
    $image_sql = "";
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_image']['tmp_name'];
        $clean_name = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', basename($_FILES['profile_image']['name']));
        $file_name = time() . '_img_' . $clean_name;
        $file_dest = $upload_dir . $file_name;
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && move_uploaded_file($file_tmp, $file_dest)) {
            $image_sql = ", profile_image = '" . $conn->real_escape_string($file_dest) . "'";
        }
    }

    // Update Users Table
    $sql_user = "UPDATE users SET name = ?, github = ?, tagline = ?, bio = ?, phone = ?, location = ?, career_objective = ?, linkedin = ? $resume_sql $image_sql WHERE id = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("ssssssssi", $name, $github, $tagline, $bio, $phone, $location, $career_objective, $linkedin, $user_id);
    $stmt->execute();

    // Re-insert Skills
    $conn->query("DELETE FROM skills WHERE user_id = $user_id");
    $skills_array = array_map('trim', explode(',', $skills_raw));
    $stmt_skill = $conn->prepare("INSERT INTO skills (user_id, skill_name) VALUES (?, ?)");
    foreach ($skills_array as $skill) {
        if (!empty($skill)) {
            $stmt_skill->bind_param("is", $user_id, $skill);
            $stmt_skill->execute();
        }
    }

    // Re-insert Projects
    $conn->query("DELETE FROM projects WHERE user_id = $user_id");
    $stmt_proj = $conn->prepare("INSERT INTO projects (user_id, project_title, description, technologies, github_link, live_demo) VALUES (?, ?, ?, ?, ?, ?)");
    for ($i = 0; $i < count($project_titles); $i++) {
        $title = $project_titles[$i];
        $desc = $project_descs[$i];
        $tech = isset($project_techs[$i]) ? $project_techs[$i] : '';
        $git_link = isset($project_githubs[$i]) ? $project_githubs[$i] : '';
        $demo_link = isset($project_demos[$i]) ? $project_demos[$i] : '';
        if (!empty($title) && !empty($desc)) {
            $stmt_proj->bind_param("isssss", $user_id, $title, $desc, $tech, $git_link, $demo_link);
            $stmt_proj->execute();
        }
    }

    // Generate static HTML portfolio
    require_once 'generate_static.php';
    generate_static_portfolio($user_id, $conn);

    // Redirect to dashboard
    header("Location: dashboard.php");
    exit();
}
?>
