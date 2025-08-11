<?php
require 'db_config.php';
require 'auth_check.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $team_name = $_POST['team_name'];
    $logo_url = null;

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $target_dir = "images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_ext = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
        $new_filename = uniqid('logo_', true) . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            $logo_url = $target_file; // save full relative path
        } else {
            $message = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO teams (team_name, logo_url) VALUES (?, ?)");
        $stmt->bind_param("ss", $team_name, $logo_url);

        if ($stmt->execute()) {
            header("Location: manage_teams.php");
            exit();
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Team</title>
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
        
        <h1>Add New Team</h1>
        <?php if($message): ?><p class="error"><?php echo $message; ?></p><?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <label for="team_name">Team Name:</label>
            <input type="text" id="team_name" name="team_name" required>
            
            <label for="logo">Team Logo:</label>
            <input type="file" id="logo" name="logo" accept="image/*">
            
            <button type="submit">Add Team</button>
        </form>
        <a href="manage_teams.php">Back to Teams List</a>
    </div>
</body>
</html>
