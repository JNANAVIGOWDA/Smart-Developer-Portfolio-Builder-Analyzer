<?php
// Function to generate a static HTML portfolio for a user
function generate_static_portfolio($user_id, $conn) {
    // Fetch user to generate the filename
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    $user = $result->fetch_assoc();
    
    // Create clean filename based on name
    // Extracts first name and removes non-alphanumeric characters
    $name_parts = explode(' ', trim($user['name']));
    $clean_name = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name_parts[0]));
    
    if (empty($clean_name)) {
        $clean_name = "user" . $user_id;
    }
    $filename = $clean_name . '.html'; // e.g., jnanavi.html
    
    // Set the GET parameter so portfolio.php knows which user to render
    $_GET['user_id'] = $user_id;
    $_GET['is_generating'] = true;
    
    // Start output buffering
    ob_start();
    
    // Include the dynamic portfolio file to render it
    // Using require instead of require_once in case we generate multiple in one request
    include 'portfolio.php'; 
    
    // Capture the generated HTML
    $html_content = ob_get_clean();
    
    // Fix relative paths so they point to the root directory
    // Because the static file is inside /user_portfolios/
    $html_content = str_replace('href="css/', 'href="../css/', $html_content);
    $html_content = str_replace('src="js/', 'src="../js/', $html_content);
    $html_content = str_replace('src="uploads/', 'src="../uploads/', $html_content);
    $html_content = str_replace('href="uploads/', 'href="../uploads/', $html_content);
    
    // Save the file
    $dir = __DIR__ . '/user_portfolios/';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    
    $file_path = $dir . $filename;
    file_put_contents($file_path, $html_content);
    
    return 'user_portfolios/' . $filename;
}
?>
