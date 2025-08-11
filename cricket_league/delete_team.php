<?php
require 'db_config.php';
require 'auth_check.php';

// Check if ID is set
if (isset($_GET['id'])) {
    $team_id = $_GET['id'];

    // Prepare a delete statement
    $stmt = $conn->prepare("DELETE FROM teams WHERE team_id = ?");
    $stmt->bind_param("i", $team_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the teams management page after successful deletion
        header("Location: manage_teams.php");
        exit();
    } else {
        // If deletion fails, display an error
        die("Error deleting record: " . $stmt->error);
    }

    $stmt->close();
} else {
    // If no ID is provided, redirect back
    header("Location: manage_teams.php");
    exit();
}
?>