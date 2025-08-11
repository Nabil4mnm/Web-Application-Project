<?php
require 'db_config.php';
require 'auth_check.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Players</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .btn-export {
            display: inline-block;
            padding: 8px 15px;
            margin: 10px 0 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .btn-export:hover {
            background-color: #218838;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this player?");
        }
    </script>
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
        
        <h1>Manage Players</h1>
        <a href="add_player.php" class="btn-add">Add New Player</a>
        <!-- Export Players CSV Button -->
        <a href="export_players.php" class="btn-export" target="_blank" rel="noopener noreferrer">📥 Export Players CSV</a>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Player Name</th>
                <th>Role</th>
                <th>Team</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT p.player_id, p.player_name, p.role, t.team_name 
                    FROM players p 
                    JOIN teams t ON p.team_id = t.team_id 
                    ORDER BY t.team_name, p.player_name";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["player_id"] . "</td>";
                    echo "<td><a href='player_stats.php?id=" . $row["player_id"] . "'>" . htmlspecialchars($row["player_name"]) . "</a></td>";
                    echo "<td>" . htmlspecialchars($row["role"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["team_name"]) . "</td>";
                    echo "<td>
                            <a href='edit_player.php?id=" . $row["player_id"] . "' class='btn-edit'>Edit</a> | 
                            <a href='delete_player.php?id=" . $row["player_id"] . "' class='btn-delete' onclick='return confirmDelete();'>Delete</a> | 
                            <a href='player_stats.php?id=" . $row["player_id"] . "' class='btn-view'>View Stats</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No players found</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
