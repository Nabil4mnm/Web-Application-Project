<?php
require 'db_config.php';
require 'auth_check.php';

if (isset($_GET['id'])) {
    $match_id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM matches WHERE match_id = ?");
    $stmt->bind_param("i", $match_id);

    if ($stmt->execute()) {
        header("Location: manage_matches.php");
        exit();
    } else {
        die("Error deleting record: " . $stmt->error);
    }
    $stmt->close();
} else {
    header("Location: manage_matches.php");
    exit();
}
?>