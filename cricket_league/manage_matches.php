<?php
require 'db_config.php';
require 'auth_check.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Matches</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this match? All associated scores will also be removed.");
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_teams.php">Teams</a>
            <a href="manage_players.php">Players</a>
            <a href="manage_matches.php">Matches</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
        
        <h1>Manage Matches</h1>
        <a href="add_match.php" class="btn-add">Schedule New Match</a>
        
        <table>
            <tr>
                <th>Fixture</th>
                <th>Date & Time</th>
                <th>Venue</th>
                <th>Status</th>
                <th>Result</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT m.match_id, t1.team_name as team1_name, t2.team_name as team2_name, m.match_date, m.venue, m.result_summary 
                    FROM matches m
                    JOIN teams t1 ON m.team1_id = t1.team_id
                    JOIN teams t2 ON m.team2_id = t2.team_id
                    ORDER BY m.match_date DESC";
            $result = $conn->query($sql);

            $now = new DateTime();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $matchDate = new DateTime($row["match_date"]);
                    // Determine match status
                    if (empty($row["result_summary"])) {
                        if ($matchDate > $now) {
                            $status = "Upcoming";
                        } elseif ($matchDate <= $now && $matchDate->add(new DateInterval('PT6H')) > $now) {
                            // Assuming match duration ~6 hours
                            $status = "Live";
                        } else {
                            $status = "Completed (No result)";
                        }
                    } else {
                        $status = "Completed";
                    }

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["team1_name"]) . " vs " . htmlspecialchars($row["team2_name"]) . "</td>";
                    echo "<td>" . date('D, M j, Y, g:i A', strtotime($row["match_date"])) . "</td>";
                    echo "<td>" . htmlspecialchars($row["venue"]) . "</td>";
                    echo "<td>" . $status . "</td>";
                    echo "<td>" . htmlspecialchars($row["result_summary"] ?: 'Pending') . "</td>";
                    echo "<td>
                            <a href='update_result.php?id=" . $row["match_id"] . "' class='btn-update'>Update Result</a> |
                            <a href='edit_match.php?id=" . $row["match_id"] . "' class='btn-edit'>Edit Schedule</a> | 
                            <a href='view_scorecard.php?id=" . $row["match_id"] . "' class='btn-scorecard'>Scorecard</a> |
                            <a href='delete_match.php?id=" . $row["match_id"] . "' class='btn-delete' onclick='return confirmDelete();'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No matches scheduled</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
