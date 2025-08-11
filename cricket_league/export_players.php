<?php
require 'db_config.php';
require 'auth_check.php';

// Set headers to download file rather than displayed
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=players_export.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('Player ID', 'Player Name', 'Role', 'Team Name'));

// Fetch the data
$sql = "SELECT p.player_id, p.player_name, p.role, t.team_name FROM players p JOIN teams t ON p.team_id = t.team_id ORDER BY t.team_name, p.player_name";
$result = $conn->query($sql);

// Loop over the rows, outputting them
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>