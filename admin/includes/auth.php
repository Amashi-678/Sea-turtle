<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {

    header("Location: /sea_turtle_project/pages/login.php");
    exit;

}

?>