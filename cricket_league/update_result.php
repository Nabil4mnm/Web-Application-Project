<?php
require 'db_config.php';
require 'auth_check.php';

$match_id = $_GET['id'];
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $result_summary = $_POST['result_summary'];

    // Update match result summary
    $stmt = $conn->prepare("UPDATE matches SET result_summary = ? WHERE match_id = ?");
    $stmt->bind_param("si", $result_summary, $match_id);
    $stmt->execute();

    $player_ids = $_POST['player_id'];
    $runs_scored = $_POST['runs'];
    $wickets_taken = $_POST['wickets'];

    // Prepare statements to check, update, or insert player scores
    $check_stmt = $conn->prepare("SELECT score_id FROM player_scores WHERE match_id = ? AND player_id = ?");
    $update_stmt = $conn->prepare("UPDATE player_scores SET runs_scored = ?, wickets_taken = ? WHERE match_id = ? AND player_id = ?");
    $insert_stmt = $conn->prepare("INSERT INTO player_scores (match_id, player_id, runs_scored, wickets_taken) VALUES (?, ?, ?, ?)");

    for ($i = 0; $i < count($player_ids); $i++) {
        $runs = isset($runs_scored[$i]) && $runs_scored[$i] !== '' ? (int)$runs_scored[$i] : 0;
        $wickets = isset($wickets_taken[$i]) && $wickets_taken[$i] !== '' ? (int)$wickets_taken[$i] : 0;

        // Check if player score exists for this match
        $check_stmt->bind_param("ii", $match_id, $player_ids[$i]);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            // Update existing player score
            $update_stmt->bind_param("iiii", $runs, $wickets, $match_id, $player_ids[$i]);
            $update_stmt->execute();
        } else {
            // Insert new player score
            $insert_stmt->bind_param("iiii", $match_id, $player_ids[$i], $runs, $wickets);
            $insert_stmt->execute();
        }
    }

    $message = "✅ Match results and player stats updated successfully!";

    // Close statements
    $check_stmt->close();
    $update_stmt->close();
    $insert_stmt->close();
}

// Fetch match details for display
$match_sql = "SELECT t1.team_name as team1, t2.team_name as team2, m.result_summary 
              FROM matches m 
              JOIN teams t1 ON m.team1_id = t1.team_id 
              JOIN teams t2 ON m.team2_id = t2.team_id 
              WHERE m.match_id = ?";
$stmt = $conn->prepare($match_sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();

// Fetch players from both teams for this match
$players_sql = "SELECT p.player_id, p.player_name, t.team_name 
                FROM players p 
                JOIN teams t ON p.team_id = t.team_id 
                WHERE p.team_id IN (SELECT team1_id FROM matches WHERE match_id = ?) 
                   OR p.team_id IN (SELECT team2_id FROM matches WHERE match_id = ?) 
                ORDER BY t.team_name, p.player_name";
$stmt = $conn->prepare($players_sql);
$stmt->bind_param("ii", $match_id, $match_id);
$stmt->execute();
$players = $stmt->get_result();

// Fetch existing player_scores to prefill inputs
$scores_sql = "SELECT player_id, runs_scored, wickets_taken FROM player_scores WHERE match_id = ?";
$stmt = $conn->prepare($scores_sql);
$stmt->bind_param("i", $match_id);
$stmt->execute();
$scores_result = $stmt->get_result();

$existing_scores = [];
while ($row = $scores_result->fetch_assoc()) {
    $existing_scores[$row['player_id']] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Match Result</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>🏏 Update Result: <?= htmlspecialchars($match['team1']) ?> vs <?= htmlspecialchars($match['team2']) ?></h1>

        <?php if ($message): ?>
            <div class="success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <label for="result_summary"><strong>Result Summary:</strong></label>
            <input type="text" id="result_summary" name="result_summary" value="<?= htmlspecialchars($match['result_summary'] ?? '') ?>" placeholder="e.g., Team A won by 20 runs" required>

            <h2>Player Performance</h2>
            <table>
                <tr>
                    <th>Player</th>
                    <th>Team</th>
                    <th>Runs Scored</th>
                    <th>Wickets Taken</th>
                </tr>
                <?php while ($player = $players->fetch_assoc()): ?>
                    <?php
                    $player_score = $existing_scores[$player['player_id']] ?? ['runs_scored' => '', 'wickets_taken' => ''];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($player['player_name']) ?></td>
                        <td><?= htmlspecialchars($player['team_name']) ?></td>
                        <input type="hidden" name="player_id[]" value="<?= $player['player_id'] ?>">
                        <td><input type="number" name="runs[]" min="0" value="<?= htmlspecialchars($player_score['runs_scored']) ?>" placeholder="0"></td>
                        <td><input type="number" name="wickets[]" min="0" value="<?= htmlspecialchars($player_score['wickets_taken']) ?>" placeholder="0"></td>
                    </tr>
                <?php endwhile; ?>
            </table>
            <button type="submit">💾 Update Results</button>
        </form>

        <a href="manage_matches.php">← Back to Matches</a>
    </div>
</body>
</html>
