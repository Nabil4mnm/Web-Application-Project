<?php
require 'db_config.php';
require 'auth_check.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>League Leaderboard</title>
    <link rel="stylesheet" href="css/style.css">
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
        <h1>League Leaderboard</h1>

        <h2>Top 5 Run Scorers</h2>
        <table>
            <tr><th>Rank</th><th>Player</th><th>Team</th><th>Total Runs</th></tr>
            <?php
            $sql_runs = "SELECT p.player_name, t.team_name, SUM(ps.runs_scored) as total_runs FROM player_scores ps JOIN players p ON ps.player_id = p.player_id JOIN teams t ON p.team_id = t.team_id GROUP BY ps.player_id ORDER BY total_runs DESC LIMIT 5";
            $result = $conn->query($sql_runs);
            $rank = 1;
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>{$rank}</td><td>" . htmlspecialchars($row['player_name']) . "</td><td>" . htmlspecialchars($row['team_name']) . "</td><td>" . $row['total_runs'] . "</td></tr>";
                $rank++;
            }
            ?>
        </table>

        <h2>Top 5 Wicket Takers</h2>
        <table>
            <tr><th>Rank</th><th>Player</th><th>Team</th><th>Total Wickets</th></tr>
            <?php
            $sql_wickets = "SELECT p.player_name, t.team_name, SUM(ps.wickets_taken) as total_wickets FROM player_scores ps JOIN players p ON ps.player_id = p.player_id JOIN teams t ON p.team_id = t.team_id GROUP BY ps.player_id ORDER BY total_wickets DESC LIMIT 5";
            $result = $conn->query($sql_wickets);
            $rank = 1;
            while($row = $result->fetch_assoc()) {
                echo "<tr><td>{$rank}</td><td>" . htmlspecialchars($row['player_name']) . "</td><td>" . htmlspecialchars($row['team_name']) . "</td><td>" . $row['total_wickets'] . "</td></tr>";
                $rank++;
            }
            ?>
        </table>
    </div>
</body>
</html>