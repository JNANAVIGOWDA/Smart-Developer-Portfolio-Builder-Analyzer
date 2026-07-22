<?php
header('Content-Type: application/json');

if (!isset($_GET['username'])) {
    echo json_encode(['error' => 'Username not provided']);
    exit;
}

$username = $_GET['username'];
$url = "https://api.github.com/users/" . urlencode($username);

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// GitHub API requires a User-Agent header
curl_setopt($ch, CURLOPT_USERAGENT, 'Smart-Portfolio-Analyzer');

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpcode == 200) {
    echo $response;
} else {
    echo json_encode(['error' => 'GitHub profile not found or API limit reached']);
}
?>
