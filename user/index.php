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
    <title>Family App - Home</title>
    <!-- Include Bootstrap CSS from CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <a class="navbar-brand" href="index.php">Leahy's App</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <!-- Adjust the active class dynamically as needed -->
            <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="dreams.php">Dreams</a></li>
            <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
            <li class="nav-item"><a class="nav-link" href="polls.php">Polls</a></li>

        </ul>
    </div>
</nav>


    <div class="container">
        <!-- Page Content -->
        <h1>Welcome to the Leahy Family App</h1>
        <p class="text-center">A place for us Leahy's to share ideas, dreams and vote on topics!.</p>
        <!-- Add any additional content here -->
    </div>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Family App
    </footer>

    <!-- Include Bootstrap JS and dependencies (jQuery and Popper.js) from CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>