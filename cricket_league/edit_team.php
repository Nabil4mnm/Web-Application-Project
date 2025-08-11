<?php
require 'db_config.php';
require 'auth_check.php';

$message = '';
$team_id = $_GET['id'] ?? null;
if (!$team_id) {
    die("Team ID not specified.");
}

// Fetch current team data to pre-fill the form
$stmt = $conn->prepare("SELECT team_name, logo_url, captain_id, wicket_keeper_id FROM teams WHERE team_id = ?");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$result = $stmt->get_result();
$team = $result->fetch_assoc();

if (!$team) {
    die("Team not found.");
}
$stmt->close();

// Fetch all players for this team (for captain & wk dropdown)
$players = [];
$stmt = $conn->prepare("SELECT player_id, player_name FROM players WHERE team_id = ? ORDER BY player_name");
$stmt->bind_param("i", $team_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $players[] = $row;
}
$stmt->close();

// Handle form submission for updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = $_POST['team_name'];
    $captain_id = $_POST['captain_id'] ?: null;
    $wicket_keeper_id = $_POST['wicket_keeper_id'] ?: null;

    // Build query dynamically with logo upload support
    $params = [];
    $types = '';
    $sql = "UPDATE teams SET team_name = ?, captain_id = ?, wicket_keeper_id = ?";
    $params = [$team_name, $captain_id, $wicket_keeper_id];
    $types = 'sii';

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $file_ext = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid('logo_', true) . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;
        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $sql .= ", logo_url = ?";
            $params[] = $target_file;
            $types .= 's';

            // Delete old logo if exists
            $old_logo = $_POST['old_logo'] ?? '';
            if ($old_logo && file_exists($old_logo)) {
                unlink($old_logo);
            }
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }

    $sql .= " WHERE team_id = ?";
    $params[] = $team_id;
    $types .= 'i';

    if (empty($message)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            header("Location: manage_teams.php");
            exit();
        } else {
            $message = "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Team</title>
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
    
    <h1>Edit Team</h1>
    <?php if($message): ?><p class="error"><?php echo $message; ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="team_name">Team Name:</label>
        <input type="text" id="team_name" name="team_name" value="<?php echo htmlspecialchars($team['team_name']); ?>" required>

        <label for="logo">Current Logo:</label><br>
        <?php if (!empty($team['logo_url']) && file_exists($team['logo_url'])): ?>
            <img src="<?php echo htmlspecialchars($team['logo_url']); ?>" alt="Team Logo" style="max-width: 150px; max-height: 150px; border-radius:6px; box-shadow:0 0 5px rgba(0,0,0,0.2);"><br>
            <input type="hidden" name="old_logo" value="<?php echo htmlspecialchars($team['logo_url']); ?>">
        <?php else: ?>
            <p>No logo uploaded.</p>
        <?php endif; ?>

        <label for="logo">Change Logo (optional):</label>
        <input type="file" id="logo" name="logo" accept="image/*">

        <label for="captain_id">Team Captain:</label>
        <select name="captain_id" id="captain_id">
            <option value="">-- Select Captain --</option>
            <?php foreach ($players as $player): ?>
                <option value="<?php echo $player['player_id']; ?>" <?php if ($team['captain_id'] == $player['player_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($player['player_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="wicket_keeper_id">Wicket-Keeper:</label>
        <select name="wicket_keeper_id" id="wicket_keeper_id">
            <option value="">-- Select Wicket-Keeper --</option>
            <?php foreach ($players as $player): ?>
                <option value="<?php echo $player['player_id']; ?>" <?php if ($team['wicket_keeper_id'] == $player['player_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($player['player_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Update Team</button>
    </form>
    <a href="manage_teams.php">Back to Teams List</a>
</div>
</body>
</html>
