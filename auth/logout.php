<?php
session_start();
require_once '../db/config.php';

// Destroy session and redirect
session_destroy();
redirect('../index.php');
?>
