<?php
// This must be included at the top of any admin-only page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>