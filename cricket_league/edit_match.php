<?php
require 'db_config.php';
require 'auth_check.php';

$message = '';
$match_id = $_GET['id'];

// Handle form submission for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team1_id = $_POST['team1_id'];
    $team2_id = $_POST['team2_id'];
    $match_date = $_POST['match_date'];
    $venue = $_POST['venue'];

    if ($team1_id == $team2_id) {
        $message = "Error: Please select two different teams.";
    } else {
        $stmt = $conn->prepare("UPDATE matches SET team1_id = ?, team2_id = ?, match_date = ?, venue = ? WHERE match_id = ?");
        $stmt->bind_param("iissi", $team1_id, $team2_id, $match_date, $venue, $match_id);

        if ($stmt->execute()) {
            header("Location: manage_matches.php");
            exit();
        } else {
            $message = "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch current match data
$stmt = $conn->prepare("SELECT team1_id, team2_id, match_date, venue FROM matches WHERE match_id = ?");
$stmt->bind_param("i", $match_id);
$stmt->execute();
$match = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all teams for dropdowns
$teams = $conn->query("SELECT team_id, team_name FROM teams")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Match Schedule</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Match Schedule</h1>
        <?php if($message): ?><p class="error" style="color:red;"><?php echo $message; ?></p><?php endif; ?>
        
        <form method="post">
            <label for="team1_id">Team 1:</label>
            <select id="team1_id" name="team1_id" required>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo $team['team_id']; ?>" <?php if ($team['team_id'] == $match['team1_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="team2_id">Team 2:</label>
            <select id="team2_id" name="team2_id" required>
                <?php foreach ($teams as $team): ?>
                    <option value="<?php echo $team['team_id']; ?>" <?php if ($team['team_id'] == $match['team2_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($team['team_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="match_date">Match Date and Time:</label>
            <input type="datetime-local" id="match_date" name="match_date" value="<?php echo date('Y-m-d\TH:i', strtotime($match['match_date'])); ?>" required>

            <label for="venue">Venue:</label>
            <input type="text" id="venue" name="venue" value="<?php echo htmlspecialchars($match['venue']); ?>" required>

            <button type="submit">Update Schedule</button>
        </form>
        <a href="manage_matches.php">Back to Matches</a>
    </div>
</body>
</html>