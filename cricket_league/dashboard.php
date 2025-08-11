<?php
require 'db_config.php';
require 'auth_check.php';

// Match start notification (matches starting now or earlier, but no result yet)
$now = date('Y-m-d H:i:s');
$notif_sql = "SELECT m.match_id, t1.team_name AS team1, t2.team_name AS team2, m.match_date 
              FROM matches m 
              JOIN teams t1 ON m.team1_id = t1.team_id 
              JOIN teams t2 ON m.team2_id = t2.team_id 
              WHERE m.match_date <= ? AND (m.result_summary IS NULL OR m.result_summary = '')";
$notif_stmt = $conn->prepare($notif_sql);
$notif_stmt->bind_param("s", $now);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();

// Fetch total teams
$teams_result = $conn->query("SELECT COUNT(*) AS total_teams FROM teams");
$teams = $teams_result->fetch_assoc()['total_teams'] ?? 0;

// Fetch total matches
$matches_result = $conn->query("SELECT COUNT(*) AS total_matches FROM matches");
$matches = $matches_result->fetch_assoc()['total_matches'] ?? 0;

// Fetch top 5 scorers by total runs
$top_scorers_sql = "
    SELECT p.player_name, t.team_name, SUM(ps.runs_scored) AS total_runs
    FROM player_scores ps
    JOIN players p ON ps.player_id = p.player_id
    JOIN teams t ON p.team_id = t.team_id
    GROUP BY ps.player_id
    ORDER BY total_runs DESC
    LIMIT 5
";
$top_scorers_result = $conn->query($top_scorers_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .notification {
            background-color: #ffefc6;
            border-left: 6px solid #f0ad4e;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 600;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-box {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 0 6px rgba(0,0,0,0.1);
        }
        .stat-box h3 {
            font-size: 2.5rem;
            margin: 0;
            color: #007bff;
        }
        .stat-box p {
            margin: 8px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
        }
        table.top-scorers {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.top-scorers th, table.top-scorers td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table.top-scorers th {
            background-color: #007bff;
            color: white;
        }
    </style>
    <style>
        .notification {
            background-color: #ffcc00;
            border: 1px solid #e6b800;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            color: #333;
        }
        .notification h2 {
            margin-top: 0;
        }
        .notification ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Navbar -->
        <div class="navbar">
            <div>
                <a href="dashboard.php">Dashboard</a>
                <a href="manage_teams.php">Teams</a>
                <a href="manage_players.php">Players</a>
                <a href="manage_matches.php">Matches</a>
                <a href="leaderboard.php">Leaderboard</a>
            </div>
            <a href="logout.php" class="logout">Logout</a>
        </div>

        <!-- Welcome Section -->
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>This is the central management system for your cricket league. Use the navigation bar to manage different aspects of the league.</p>

        <!-- Match Start Notifications -->
        <?php if ($notif_result->num_rows > 0): ?>
            <div class="notification">
                <strong>Match Notification:</strong><br>
                <?php
                while ($notif = $notif_result->fetch_assoc()) {
                    echo htmlspecialchars($notif['team1']) . " vs " . htmlspecialchars($notif['team2']) . " — started at " . date('g:i A, M j', strtotime($notif['match_date'])) . "<br>";
                }
                ?>
            </div>
        <?php endif; ?>
        <?php
        // Fetch ongoing matches (started but no result yet)
        $sql = "SELECT m.match_id, t1.team_name as team1_name, t2.team_name as team2_name, m.match_date, m.venue 
                FROM matches m
                JOIN teams t1 ON m.team1_id = t1.team_id
                JOIN teams t2 ON m.team2_id = t2.team_id
                WHERE m.match_date <= NOW() AND (m.result_summary IS NULL OR m.result_summary = '')
                ORDER BY m.match_date ASC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $ongoing_matches = $stmt->get_result();

        if ($ongoing_matches->num_rows > 0): ?>
            <div class="notification">
                <h2>⚠️ Ongoing Matches</h2>
                <ul>
                    <?php while ($row = $ongoing_matches->fetch_assoc()): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($row['team1_name']) . " vs " . htmlspecialchars($row['team2_name']); ?></strong>
                            at <?php echo date('D, M j, g:i A', strtotime($row['match_date'])); ?>
                            - Venue: <?php echo htmlspecialchars($row['venue']); ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php endif; ?>
        <!-- Quick Actions Section -->
        <h2>Quick Actions</h2>
        <div class="quick-actions">
            <a href="add_team.php" class="quick-btn">➕ Add a New Team</a>
            <a href="add_player.php" class="quick-btn">👤 Add a New Player</a>
            <a href="add_match.php" class="quick-btn">🗓️ Schedule a New Match</a>
        </div>

        <!-- Stats Section -->
        <h2>League Stats</h2>
        <div class="stats-container">
            <div class="stat-box">
                <h3><?php echo $teams; ?></h3>
                <p>Total Teams</p>
            </div>
            <div class="stat-box">
                <h3><?php echo $matches; ?></h3>
                <p>Total Matches</p>
            </div>
            <div class="stat-box" style="flex: 2;">
                <h3>Top 5 Scorers</h3>
                <table class="top-scorers">
                    <thead>
                        <tr>
                            <th>Player</th>
                            <th>Team</th>
                            <th>Runs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($top_scorers_result->num_rows > 0) {
                            while ($row = $top_scorers_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['team_name']) . "</td>";
                                echo "<td>" . intval($row['total_runs']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No data available</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
