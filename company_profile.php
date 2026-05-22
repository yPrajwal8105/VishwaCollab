<?php
require_once 'config.php';

// Backward-compatible shim for older links using underscore naming.
$target = 'company-profile.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $target .= '?' . $_SERVER['QUERY_STRING'];
}

header('Location: ' . $target, true, 301);
exit();
