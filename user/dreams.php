<?php
require '/var/www/html/vendor/autoload.php'; // Absolute path to autoload.php

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

// AWS SDK Configuration using environment variables
$snsClient = new SnsClient([
    'version' => 'latest',
    'region' => 'us-east-1', // Ensure this is your region
    // Credentials are automatically picked up from environment variables or EC2 IAM roles
]);

// Connect to the RDS MySQL database
$servername = "family-app-db.cblynykvsyaq.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "mypassword";
$dbname = "family";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for form submission to add a new dream
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dream = $conn->real_escape_string($_POST["dream"]);
    $sql = "INSERT INTO dreams (dream) VALUES ('$dream')";
    if ($conn->query($sql) === TRUE) {
        // Dream added successfully, send SNS notification
        try {
            $result = $snsClient->publish([
                'TopicArn' => 'arn:aws:sns:us-east-1:573598993687:FamilyDreamNotifications', // Use your topic ARN
                'Message' => "A new dream has been added: $dream",
                'Subject' => 'New Dream Added to Family App',
            ]);
            $message = "New dream added successfully! Notification sent.";
        } catch (AwsException $e) {
            // Output error message if fails
            $message = "New dream added, but failed to send notification: " . $e->getMessage();
        }
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Retrieve all dreams
$result = $conn->query("SELECT * FROM dreams ORDER BY id DESC");

$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Family Dreams</title>
    <!-- Include Bootstrap CSS from CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <a class="navbar-brand" href="index.php">Family App</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"     aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item active"><a class="nav-link" href="dreams.php">Dreams</a></li>
                <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                <!-- Add other links as needed -->
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Page Content -->
        <h1 class="text-center">Leahy Family Dreams</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Display the list of dreams -->
        <?php if ($result && $result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while($row = $result->fetch_assoc()): ?>
                    <li class="list-group-item"><?php echo htmlspecialchars($row["dream"]); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="text-center">No dreams found.</p>
        <?php endif; ?>

        <!-- Form to add a new dream -->
        <div class="message-form">
            <h2>Add a Dream</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="dream">Your Dream:</label>
                    <input type="text" class="form-control" id="dream" name="dream" required>
                </div>
                <button type="submit" name="add_dream" class="btn btn-primary">Add Dream</button>
            </form>
        </div>
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