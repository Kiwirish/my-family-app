<?php
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
    <title>Family Dreams - Home</title>
    <link rel="stylesheet" href="user/css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Menu -->
        <nav>
            <a href="index.php">Home</a>
            <a href="dreams.php">Dreams</a>
            <!-- Add other links as needed -->
        </nav>

        <!-- Page Content -->
        <h1>Welcome to the Leahy Family App</h1>
        <p>Share your dreams, and images with the Leahy's.</p>
    </div>
</body>
</html>

