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
            ps.runs_scored, ps.wickets_taken, ps.balls_faced, ps.overs_bowled, ps.runs_conceded
     FROM player_scores ps
     JOIN matches m ON ps.match_id = m.match_id
     WHERE ps.player_id = ?
     ORDER BY m.match_date ASC");
$history_stmt->bind_param("i", $player_id);
$history_stmt->execute();
$match_history = $history_stmt->get_result();

$match_numbers = [];
$batting_avg_data = [];
$bowling_avg_data = [];

$total_runs = 0;
$total_outs = 0;  // Assuming each match is an 'out' for average calc - you can adapt
$total_runs_conceded = 0;
$total_wickets = 0;

$match_count = 0;
$matches = [];

while ($match = $match_history->fetch_assoc()) {
    $match_count++;
    $matches[] = $match;
    $match_numbers[] = $match_count;

    // For batting average: runs / outs
    // Here we assume 1 out per match for simplicity; adapt if you have not outs info
    $total_runs += $match['runs_scored'];
    $total_outs += 1; // You can change this if you have not_outs column

    $batting_avg = $total_outs > 0 ? round($total_runs / $total_outs, 2) : 0;
    $batting_avg_data[] = $batting_avg;

    // For bowling average: runs conceded / wickets taken (per match cumulative)
    $total_runs_conceded += $match['runs_conceded'];
    $total_wickets += $match['wickets_taken'];
    $bowling_avg = $total_wickets > 0 ? round($total_runs_conceded / $total_wickets, 2) : 0;
    $bowling_avg_data[] = $bowling_avg;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php echo htmlspecialchars($player_info['player_name']); ?> - Player Stats</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #121212;
            color: #eee;
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #1e1e1e;
            padding: 25px;
            border-radius: 10px;
        }
        h1, h2 {
            color: #ff6666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #222;
            color: #eee;
        }
        th, td {
            padding: 10px;
            border: 1px solid #444;
            text-align: center;
        }
        th {
            background: #333;
        }
        a {
            color: #4da3ff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .chart-container {
            margin: 30px 0;
            background: #222;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.7);
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
            <canvas id="battingChart" height="300"></canvas>
        </div>

        <div class="chart-container">
            <canvas id="bowlingChart" height="300"></canvas>
        </div>

        <h2>Match History</h2>
        <table>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Fixture</th>
                <th>Venue</th>
                <th>Runs Scored</th>
                <th>Wickets Taken</th>
            </tr>
            <?php if (!empty($matches)): ?>
                <?php foreach ($matches as $index => $match): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo date('M j, Y', strtotime($match['match_date'])); ?></td>
                        <td><?php echo htmlspecialchars($match['team1']) . " vs " . htmlspecialchars($match['team2']); ?></td>
                        <td><?php echo htmlspecialchars($match['venue']); ?></td>
                        <td><?php echo $match['runs_scored']; ?></td>
                        <td><?php echo $match['wickets_taken']; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No match data found.</td></tr>
            <?php endif; ?>
        </table>

        <br>
        <a href="manage_players.php">Back to Players List</a>
    </div>

    <script>
        const matchNumbers = <?php echo json_encode($match_numbers); ?>;
        const battingAvgData = <?php echo json_encode($batting_avg_data); ?>;
        const bowlingAvgData = <?php echo json_encode($bowling_avg_data); ?>;

        // Batting average chart
        const ctxBatting = document.getElementById('battingChart').getContext('2d');
        new Chart(ctxBatting, {
            type: 'line',
            data: {
                labels: matchNumbers,
                datasets: [{
                    label: 'Batting Average',
                    data: battingAvgData,
                    borderColor: '#4da3ff',
                    backgroundColor: 'rgba(77, 163, 255, 0.3)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: '#4da3ff',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#eee' } },
                    title: {
                        display: true,
                        text: 'Number of Matches vs Batting Average',
                        color: '#eee',
                        font: { size: 18 }
                    }
                },
                scales: {
                    x: {
                        type: 'category',
                        title: {
                            display: true,
                            text: 'Match Number',
                            color: '#eee'
                        },
                        ticks: { color: '#eee' },
                        grid: { color: '#444' }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Batting Average',
                            color: '#eee'
                        },
                        ticks: { color: '#eee' },
                        grid: { color: '#444' }
                    }
                }
            }
        });

        // Bowling average chart
        const ctxBowling = document.getElementById('bowlingChart').getContext('2d');
        new Chart(ctxBowling, {
            type: 'line',
            data: {
                labels: matchNumbers,
                datasets: [{
                    label: 'Bowling Average',
                    data: bowlingAvgData,
                    borderColor: '#4dff88',
                    backgroundColor: 'rgba(77, 255, 136, 0.3)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: '#4dff88',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { labels: { color: '#eee' } },
                    title: {
                        display: true,
                        text: 'Number of Matches vs Bowling Average',
                        color: '#eee',
                        font: { size: 18 }
                    }
                },
                scales: {
                    x: {
                        type: 'category',
                        title: {
                            display: true,
                            text: 'Match Number',
                            color: '#eee'
                        },
                        ticks: { color: '#eee' },
                        grid: { color: '#444' }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Bowling Average',
                            color: '#eee'
                        },
                        ticks: { color: '#eee' },
                        grid: { color: '#444' }
                    }
                }
            }
        });
    </script>
</body>
</html>
