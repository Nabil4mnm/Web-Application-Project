<?php
// Start session for user management
session_start();

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cricket_league";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>