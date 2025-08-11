<?php
require 'db_config.php';
require 'auth_check.php';

if (!isset($_GET['id'])) {
    die("Match ID not specified.");
}
$match_id = intval($_GET['id']);

// Fetch match info and teams
$sql = "SELECT m.match_id, m.match_date, m.venue, m.result_summary, 
               t1.team_id AS team1_id, t1.team_name AS team1_name, 
               t2.team_id AS team2_id, t2.team_name AS team2_name
        FROM matches m
        JOIN teams t1 ON m.team1_id = t1.team_id
        JOIN teams t2 ON m.team2_id = t2.team_id
        WHERE m.match_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();
if (!$match) {
    die("Match not found.");
}

// Function to get batting stats for a team
function getBattingStats($conn, $match_id, $team_id) {
    $sql = "SELECT p.player_name, ps.runs_scored, ps.balls_faced, ps.fours, ps.sixes
            FROM player_scores ps
            JOIN players p ON ps.player_id = p.player_id
            WHERE ps.match_id = ? AND p.team_id = ? AND ps.balls_faced > 0
            ORDER BY ps.runs_scored DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $match_id, $team_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Function to get bowling stats for a team
function getBowlingStats($conn, $match_id, $team_id) {
    $sql = "SELECT p.player_name, ps.overs_bowled, ps.maidens, ps.runs_conceded, ps.wickets_taken
            FROM player_scores ps
            JOIN players p ON ps.player_id = p.player_id
            WHERE ps.match_id = ? AND p.team_id = ? AND ps.overs_bowled > 0
            ORDER BY ps.wickets_taken DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $match_id, $team_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Calculate strike rate function
function strikeRate($runs, $balls) {
    if ($balls == 0) return 0;
    return round(($runs / $balls) * 100, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Scorecard: <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?></title>
    <link rel="stylesheet" href="css/style.css" />
    <style>
        /* Table base styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff; /* White background for better contrast */
            color: #222; /* Dark text */
            font-size: 16px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px 12px;
            text-align: center;
        }
        th {
            background-color: #222; /* Dark header */
            color: #fff; /* White text in header */
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f7f7f7; /* Light grey alternate rows */
        }
        tr:hover {
            background-color: #e0e0e0; /* Hover highlight */
        }
        h2 {
            margin-top: 40px;
            color: #333;
        }
        a.back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        a.back-link:hover {
            text-decoration: underline;
        }
        /* Scoped Scorecard Styles */
        .scorecard table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 30px;
        background-color: #ffffff !important; /* Strong white bg */
        color: #222222 !important;            /* Strong dark text */
        font-size: 16px;
        }

        .scorecard th, .scorecard td {
        border: 1px solid #ccc !important;
        padding: 8px 12px !important;
        text-align: center !important;
        background-color: #ffffff !important;
        color: #222222 !important;
        }

        .scorecard th {
        background-color: #222222 !important; /* Dark header */
        color: #ffffff !important;            /* White text */
        font-weight: bold !important;
        }

        .scorecard tr:nth-child(even) {
        background-color: #f7f7f7 !important; /* Light gray rows */
        }

        .scorecard tr:hover {
        background-color: #e0e0e0 !important; /* Hover highlight */
        }

    </style>
</head>
<body>
<div class="container">
    <h1>Scorecard: <?= htmlspecialchars($match['team1_name']) ?> vs <?= htmlspecialchars($match['team2_name']) ?></h1>
    <p><strong>Date:</strong> <?= date('D, M j, Y, g:i A', strtotime($match['match_date'])) ?></p>
    <p><strong>Venue:</strong> <?= htmlspecialchars($match['venue']) ?></p>
    <p><strong>Result:</strong> <?= htmlspecialchars($match['result_summary'] ?? 'Pending') ?></p>

    <!-- Batting & Bowling for Team 1 -->
    <h2>Batting - <?= htmlspecialchars($match['team1_name']) ?></h2>
    <table>
        <tr><th>Player</th><th>Runs</th><th>Balls</th><th>4s</th><th>6s</th><th>Strike Rate</th></tr>
        <?php
        $batting1 = getBattingStats($conn, $match_id, $match['team1_id']);
        if ($batting1->num_rows > 0) {
            while ($row = $batting1->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                echo "<td>" . $row['runs_scored'] . "</td>";
                echo "<td>" . $row['balls_faced'] . "</td>";
                echo "<td>" . $row['fours'] . "</td>";
                echo "<td>" . $row['sixes'] . "</td>";
                echo "<td>" . strikeRate($row['runs_scored'], $row['balls_faced']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No batting data available.</td></tr>";
        }
        ?>
    </table>

    <h2>Bowling - <?= htmlspecialchars($match['team2_name']) ?></h2>
    <table>
        <tr><th>Player</th><th>Overs</th><th>Maidens</th><th>Runs Conceded</th><th>Wickets</th></tr>
        <?php
        $bowling2 = getBowlingStats($conn, $match_id, $match['team2_id']);
        if ($bowling2->num_rows > 0) {
            while ($row = $bowling2->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                echo "<td>" . $row['overs_bowled'] . "</td>";
                echo "<td>" . $row['maidens'] . "</td>";
                echo "<td>" . $row['runs_conceded'] . "</td>";
                echo "<td>" . $row['wickets_taken'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No bowling data available.</td></tr>";
        }
        ?>
    </table>

    <!-- Batting & Bowling for Team 2 -->
    <h2>Batting - <?= htmlspecialchars($match['team2_name']) ?></h2>
    <table>
        <tr><th>Player</th><th>Runs</th><th>Balls</th><th>4s</th><th>6s</th><th>Strike Rate</th></tr>
        <?php
        $batting2 = getBattingStats($conn, $match_id, $match['team2_id']);
        if ($batting2->num_rows > 0) {
            while ($row = $batting2->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                echo "<td>" . $row['runs_scored'] . "</td>";
                echo "<td>" . $row['balls_faced'] . "</td>";
                echo "<td>" . $row['fours'] . "</td>";
                echo "<td>" . $row['sixes'] . "</td>";
                echo "<td>" . strikeRate($row['runs_scored'], $row['balls_faced']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No batting data available.</td></tr>";
        }
        ?>
    </table>

    <h2>Bowling - <?= htmlspecialchars($match['team1_name']) ?></h2>
    <table>
        <tr><th>Player</th><th>Overs</th><th>Maidens</th><th>Runs Conceded</th><th>Wickets</th></tr>
        <?php
        $bowling1 = getBowlingStats($conn, $match_id, $match['team1_id']);
        if ($bowling1->num_rows > 0) {
            while ($row = $bowling1->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['player_name']) . "</td>";
                echo "<td>" . $row['overs_bowled'] . "</td>";
                echo "<td>" . $row['maidens'] . "</td>";
                echo "<td>" . $row['runs_conceded'] . "</td>";
                echo "<td>" . $row['wickets_taken'] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No bowling data available.</td></tr>";
        }
        ?>
    </table>

    <a href="manage_matches.php" class="back-link">← Back to Matches</a>
</div>
</body>
</html>
