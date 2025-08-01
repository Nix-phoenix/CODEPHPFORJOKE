<?php
session_start();

function requireLogin() {
    if (!isset($_SESSION['emp_id'])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        echo "🚫 You do not have permission to access this page.";
        exit;
    }
}
?>
