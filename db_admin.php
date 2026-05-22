<?php
function adminer_object() {
    class AdminerSoftware extends Adminer {
        function login($login, $password) {
            // Bypass password check for local development
            return true;
        }
    }
    return new AdminerSoftware;
}

// Include original Adminer
include "./adminer-4.8.1.php";
?>
