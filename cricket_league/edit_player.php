<?php
require 'db_config.php';
require 'auth_check.php';

$message = '';
$player_id = $_GET['id'];

// Handle form submission for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player_name = $_POST['player_name'];
    $role = $_POST['role'];
    $team_id = $_POST['team_id'];
    
    $stmt = $conn->prepare("UPDATE players SET player_name = ?, role = ?, team_id = ? WHERE player_id = ?");
    $stmt->bind_param("ssii", $player_name, $role, $team_id, $player_id);

    if ($stmt->execute()) {
        header("Location: manage_players.php");
        exit();
    } else {
        $message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch current player data
$stmt = $conn->prepare("SELECT player_name, role, team_id FROM players WHERE player_id = ?");
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
$player = $result->fetch_assoc();

if (!$player) {
    die("Player not found.");
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Player</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Player</h1>
        <?php if($message): ?><p class="error"><?php echo $message; ?></p><?php endif; ?>

        <form method="post">
            <label for="player_name">Player Name:</label>
            <input type="text" id="player_name" name="player_name" value="<?php echo htmlspecialchars($player['player_name']); ?>" required>
            
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="Batsman" <?php if($player['role'] == 'Batsman') echo 'selected'; ?>>Batsman</option>
                <option value="Bowler" <?php if($player['role'] == 'Bowler') echo 'selected'; ?>>Bowler</option>
                <option value="All-Rounder" <?php if($player['role'] == 'All-Rounder') echo 'selected'; ?>>All-Rounder</option>
                <option value="Wicketkeeper" <?php if($player['role'] == 'Wicketkeeper') echo 'selected'; ?>>Wicketkeeper</option>
            </select>
            
            <label for="team_id">Team:</label>
            <select id="team_id" name="team_id" required>
                <?php
                $teams_result = $conn->query("SELECT team_id, team_name FROM teams ORDER BY team_name");
                while ($team = $teams_result->fetch_assoc()) {
                    $selected = ($team['team_id'] == $player['team_id']) ? 'selected' : '';
                    echo "<option value='" . $team['team_id'] . "' $selected>" . htmlspecialchars($team['team_name']) . "</option>";
                }
                ?>
            </select>
            
            <button type="submit">Update Player</button>
        </form>
        <a href="manage_players.php">Back to Players List</a>
    </div>
</body>
</html>