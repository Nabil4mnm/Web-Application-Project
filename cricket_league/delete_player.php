<?php
require 'db_config.php';
require 'auth_check.php';

if (isset($_GET['id'])) {
    $player_id = $_GET['id'];

    // Deleting the player will also delete their scores due to ON DELETE CASCADE
    $stmt = $conn->prepare("DELETE FROM players WHERE player_id = ?");
    $stmt->bind_param("i", $player_id);

    if ($stmt->execute()) {
        header("Location: manage_players.php");
        exit();
    } else {
        die("Error deleting record: " . $stmt->error);
    }
    $stmt->close();
} else {
    header("Location: manage_players.php");
    exit();
}
?>