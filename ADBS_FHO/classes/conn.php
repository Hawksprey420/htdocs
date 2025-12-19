<?php 
$servername = "localhost";
$username = "root";
$password = "root123"; // Needs to be empty prior to github posting
$dbname = "hrm_project_finals";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";
?>