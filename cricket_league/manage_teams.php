<?php
require 'db_config.php';
require 'auth_check.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teams</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete this team? This will also delete all associated players and matches.");
        }
    </script>
    <style>
        /* Optional: size the logos */
        img.team-logo {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
            border-radius: 6px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
    </style>
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
        
        <h1>Manage Teams</h1>
        <a href="add_team.php" class="btn-add">Add New Team</a>
        
        <table>
            <tr>
                <th>ID</th>
                <th>Team Name</th>
                <th>Logo</th>
                <th>Captain</th>
                <th>Wicket-Keeper</th>
                <th>Actions</th>
            </tr>
            <?php
            $sql = "SELECT 
                        t.team_id, t.team_name, t.logo_url, 
                        c.player_name AS captain_name, 
                        w.player_name AS wk_name
                    FROM teams t
                    LEFT JOIN players c ON t.captain_id = c.player_id
                    LEFT JOIN players w ON t.wk_id = w.player_id
                    ORDER BY t.team_name";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["team_id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["team_name"]) . "</td>";
                    echo "<td>";
                    if (!empty($row['logo_url']) && file_exists($row['logo_url'])) {
                        echo "<img class='team-logo' src='" . htmlspecialchars($row['logo_url']) . "' alt='Logo'>";
                    } else {
                        echo "No Logo";
                    }
                    echo "</td>";
                    echo "<td>" . htmlspecialchars($row['captain_name'] ?? 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['wk_name'] ?? 'N/A') . "</td>";
                    echo "<td>
                            <a href='edit_team.php?id=" . $row["team_id"] . "' class='btn-edit'>Edit</a> | 
                            <a href='delete_team.php?id=" . $row["team_id"] . "' class='btn-delete' onclick='return confirmDelete();'>Delete</a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No teams found</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
