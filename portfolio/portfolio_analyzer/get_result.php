<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_GET['history_id'])) {
    $history_id = intval($_GET['history_id']);
    $stmt = $conn->prepare("SELECT * FROM analysis_history WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $history_id, $user_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM analysis_history WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $row['suggestions'] = json_decode($row['suggestions']);
    
    // Determine static portfolio URL
    $stmt_user = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $user = $stmt_user->get_result()->fetch_assoc();
    
    $name_parts = explode(' ', trim($user['name']));
    $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name_parts[0]));
    if (empty($clean_name)) { $clean_name = "user" . $user_id; }
    $static_portfolio_url = 'user_portfolios/' . $clean_name . '.html';

    echo json_encode(["status" => "success", "data" => $row, "static_url" => $static_portfolio_url]);
} else {
    echo json_encode(["status" => "error", "message" => "No analysis found."]);
}
?>
