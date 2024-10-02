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
    <!-- Include Bootstrap CSS from CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-admin fixed-top">
    <a class="navbar-brand" href="index.php">Leahy's Admin</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#adminNav" 
        aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="dreams.php">Manage Dreams</a></li>
            <li class="nav-item"><a class="nav-link" href="messages.php">Manage Messages</a></li>
            <li class="nav-item"><a class="nav-link" href="polls.php">Manage Polls</a></li>
            <!-- Add other links as needed -->
        </ul>
    </div>
</nav>

    <div class="container">
        <!-- Page Content -->
        <h1>Welcome to the Leahy Family Admin Page</h1>
        <p>Administrate the Leahy Family App!</p>
    </div>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Leahy's App
    </footer>

    <!-- Include Bootstrap JS and dependencies (jQuery and Popper.js) from CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>