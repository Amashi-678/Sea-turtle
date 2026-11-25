<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    
    header("Location: /Turtle-Haven/Sea-turtle/pages/login.php");
    exit;

}

?>