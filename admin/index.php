<?php
// Start the session (if you plan to implement authentication later)
// session_start();

// Connect to the RDS MySQL database
$servername = "family-app-db.cblynykvsyaq.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "mypassword";
$dbname = "family";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Admin - Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
    <nav>
            <a href="index.php">Home</a>
            <a href="dreams.php">Manage Dreams</a>
            <!-- Add other links as needed -->
        </nav>

        <!-- Page Content -->
        <h1>Welcome to the Leahy Family Admin Page</h1>
        <p>Administrate the Leahy Family App!.</p>
    </div>
</body>
</html>
