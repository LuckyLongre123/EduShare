<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'project_db');
// define('DB_HOST', 'sql310.infinityfree.com');
// define('DB_USER', 'if0_39937351');
// define('DB_PASS', 'LSQOVrPfECgFG');
// define('DB_NAME', 'if0_39937351_project_db');
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    echo ''. $conn->connect_error;
    die("Connection failed: " . $conn->connect_error);
}else{
    $conn->select_db(DB_NAME);
}

// Helper functions
function sanitize_input($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}

function is_logged_in() {
    return isset($_SESSION['email']);
}

function get_user_role() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function redirect($url) {
    header("Location: $url");
    exit();
}
?>