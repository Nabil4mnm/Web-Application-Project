<?php
require 'db_config.php';
require 'auth_check.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("No player specified.");
}
$player_id = $_GET['id'];

// --- Player Info ---
$stmt = $conn->prepare("SELECT p.player_name, p.role, t.team_name 
                        FROM players p 
                        JOIN teams t ON p.team_id = t.team_id 
                        WHERE p.player_id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$player_info = $stmt->get_result()->fetch_assoc();
if (!$player_info) {
    die("Player not found.");
}

// --- Career Stats ---
$stats_stmt = $conn->prepare(
    "SELECT COUNT(score_id) as matches_played,
            SUM(runs_scored) as total_runs,
            MAX(runs_scored) as highest_score,
            AVG(runs_scored) as batting_average,
            SUM(wickets_taken) as total_wickets,
            SUM(balls_faced) as total_balls_faced,
            SUM(overs_bowled) as total_overs_bowled,
            SUM(runs_conceded) as total_runs_conceded
     FROM player_scores WHERE player_id = ?");
$stats_stmt->bind_param("i", $player_id);
$stats_stmt->execute();
$career_stats = $stats_stmt->get_result()->fetch_assoc();

// --- Extra Calculations ---
$strike_rate = ($career_stats['total_balls_faced'] > 0) 
    ? round(($career_stats['total_runs'] / $career_stats['total_balls_faced']) * 100, 2) 
    : 0;

$economy_rate = ($career_stats['total_overs_bowled'] > 0) 
    ? round($career_stats['total_runs_conceded'] / $career_stats['total_overs_bowled'], 2) 
    : 0;

// --- Match History ---
$history_stmt = $conn->prepare(
    "SELECT m.match_date, m.venue, 
            (SELECT t.team_name FROM teams t WHERE t.team_id = m.team1_id) as team1,
            (SELECT t.team_name FROM teams t WHERE t.team_id = m.team2_id) as team2,
            ps.runs_scored, ps.wickets_taken
     FROM player_scores ps
     JOIN matches m ON ps.match_id = m.match_id
     WHERE ps.player_id = ?
     ORDER BY m.match_date ASC");
$history_stmt->bind_param("i", $player_id);
$history_stmt->execute();
$match_history = $history_stmt->get_result();

$match_dates = [];
$runs_data = [];
$wickets_data = [];
$matches = [];
while ($match = $match_history->fetch_assoc()) {
    $match_dates[] = date('M j', strtotime($match['match_date']));
    $runs_data[] = $match['runs_scored'];
    $wickets_data[] = $match['wickets_taken'];
    $matches[] = $match;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($player_info['player_name']); ?> - Player Stats</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: auto;
            background: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: 10px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background: #1e1e1e;
            color: #fff;
        }
        table, th, td {
            border: 1px solid #444;
            padding: 10px;
        }
        th {
            background: #333;
            color: #fff;
        }
        .navbar a {
            padding: 10px 15px;
            color: white;
            background: #444;
            margin-right: 5px;
            text-decoration: none;
            border-radius: 5px;
        }
        .navbar a:hover {
            background: #222;
        }
        body {
            background: #121212;
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_players.php">Players</a>
        <a href="leaderboard.php">Leaderboard</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <div class="container">
        <h1><?php echo htmlspecialchars($player_info['player_name']); ?></h1>
        <p><strong>Team:</strong> <?php echo htmlspecialchars($player_info['team_name']); ?> | 
           <strong>Role:</strong> <?php echo htmlspecialchars($player_info['role']); ?></p>
        <hr>

        <h2>Career Statistics</h2>
        <table>
            <tr>
                <th>Matches Played</th>
                <th>Total Runs</th>
                <th>Highest Score</th>
                <th>Batting Average</th>
                <th>Total Wickets</th>
                <th>Strike Rate</th>
                <th>Economy Rate</th>
            </tr>
            <tr>
                <td><?php echo $career_stats['matches_played'] ?? 0; ?></td>
                <td><?php echo $career_stats['total_runs'] ?? 0; ?></td>
                <td><?php echo $career_stats['highest_score'] ?? 0; ?></td>
                <td><?php echo round($career_stats['batting_average'] ?? 0, 2); ?></td>
                <td><?php echo $career_stats['total_wickets'] ?? 0; ?></td>
                <td><?php echo $strike_rate; ?></td>
                <td><?php echo $economy_rate; ?></td>
            </tr>
        </table>

        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>

        <h2>Match History</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Fixture</th>
                <th>Venue</th>
                <th>Runs Scored</th>
                <th>Wickets Taken</th>
            </tr>
            <?php if (!empty($matches)): ?>
                <?php foreach ($matches as $match): ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($match['match_date'])); ?></td>
                        <td><?php echo htmlspecialchars($match['team1']) . " vs " . htmlspecialchars($match['team2']); ?></td>
                        <td><?php echo htmlspecialchars($match['venue']); ?></td>
                        <td><?php echo $match['runs_scored']; ?></td>
                        <td><?php echo $match['wickets_taken']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5">No match data found.</td></tr>
            <?php endif; ?>
        </table>

        <br>
        <a href="manage_players.php">Back to Players List</a>
    </div>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($match_dates); ?>,
                datasets: [
                    {
                        label: 'Runs Scored',
                        data: <?php echo json_encode($runs_data); ?>,
                        borderColor: '#4da3ff',
                        backgroundColor: 'rgba(77, 163, 255, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4da3ff',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    },
                    {
                        label: 'Wickets Taken',
                        data: <?php echo json_encode($wickets_data); ?>,
                        borderColor: '#4dff88',
                        backgroundColor: 'rgba(77, 255, 136, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4dff88',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Player Performance Over Matches',
                        color: '#fff',
                        font: { size: 18 }
                    },
                    legend: {
                        labels: {
                            color: '#fff'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#333',
                        titleColor: '#fff',
                        bodyColor: '#fff'
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: '#fff'
                        },
                        grid: {
                            color: '#444',
                            borderColor: '#fff'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#fff',
                            stepSize: 1
                        },
                        grid: {
                            color: '#444',
                            borderColor: '#fff'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
