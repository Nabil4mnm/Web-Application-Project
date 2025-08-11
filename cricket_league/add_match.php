<?php
require 'db_config.php';
require 'auth_check.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team1_id = $_POST['team1_id'];
    $team2_id = $_POST['team2_id'];
    $match_date = $_POST['match_date'];
    $venue = $_POST['venue'];

    if ($team1_id == $team2_id) {
        $message = "Error: A team cannot play against itself. Please select two different teams.";
    } else {
        $stmt = $conn->prepare("INSERT INTO matches (team1_id, team2_id, match_date, venue) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $team1_id, $team2_id, $match_date, $venue);

        if ($stmt->execute()) {
            header("Location: manage_matches.php");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch teams for dropdowns
$teams_result = $conn->query("SELECT team_id, team_name FROM teams ORDER BY team_name");
$teams = $teams_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Schedule New Match</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Schedule New Match</h1>
        <?php if($message): ?><p class="error" style="color:red;"><?php echo $message; ?></p><?php endif; ?>

        <form method="post">
            <label for="team1_id">Team 1:</label>
            <select id="team1_id" name="team1_id" required>
                <option value="">-- Select Team 1 --</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo $team['team_id']; ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="team2_id">Team 2:</label>
            <select id="team2_id" name="team2_id" required>
                <option value="">-- Select Team 2 --</option>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo $team['team_id']; ?>"><?php echo htmlspecialchars($team['team_name']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="match_date">Match Date and Time:</label>
            <input type="datetime-local" id="match_date" name="match_date" required>

            <label for="venue">Venue:</label>
            <input type="text" id="venue" name="venue" required>

            <button type="submit">Schedule Match</button>
        </form>
        <a href="manage_matches.php">Back to Matches</a>
    </div>
</body>
</html>