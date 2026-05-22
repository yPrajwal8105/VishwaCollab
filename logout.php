<?php
require_once 'config.php';

// Destroy session
session_destroy();

// Clear session data
$_SESSION = array();

// Redirect to home page
header("Location: index.php");
exit();
?>