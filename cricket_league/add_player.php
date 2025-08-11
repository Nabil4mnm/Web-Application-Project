<?php
require 'db_config.php';
require 'auth_check.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player_name = $_POST['player_name'];
    $role = $_POST['role'];
    $team_id = $_POST['team_id'];

    $stmt = $conn->prepare("INSERT INTO players (player_name, role, team_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $player_name, $role, $team_id);

    if ($stmt->execute()) {
        header("Location: manage_players.php");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Player</title>
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
        
        <h1>Add New Player</h1>
        <?php if($message): ?><p class="error"><?php echo $message; ?></p><?php endif; ?>

        <form method="post">
            <label for="player_name">Player Name:</label>
            <input type="text" id="player_name" name="player_name" required>
            
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="Batsman">Batsman</option>
                <option value="Bowler">Bowler</option>
                <option value="All-Rounder">All-Rounder</option>
                <option value="Wicketkeeper">Wicketkeeper</option>
            </select>
            
            <label for="team_id">Team:</label>
            <select id="team_id" name="team_id" required>
                <option value="">-- Select a Team --</option>
                <?php
                $teams_result = $conn->query("SELECT team_id, team_name FROM teams ORDER BY team_name");
                while ($team = $teams_result->fetch_assoc()) {
                    echo "<option value='" . $team['team_id'] . "'>" . htmlspecialchars($team['team_name']) . "</option>";
                }
                ?>
            </select>
            
            <button type="submit">Add Player</button>
        </form>
        <a href="manage_players.php">Back to Players List</a>
    </div>
</body>
</html>