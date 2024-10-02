<?php
// Display errors for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include Composer's autoloader
require '/var/www/html/vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

// AWS SDK Configuration
$snsClient = new SnsClient([
    'version' => 'latest',
    'region'  => 'us-east-1',
]);

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

// Initialize variables
$message = '';
$error = '';

// Check for form submission to add a new message
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_message'])) {
    $author = $conn->real_escape_string($_POST["author"]);
    $message_content = $conn->real_escape_string($_POST["message_content"]);

    $stmt = $conn->prepare("INSERT INTO messages (author, message_content) VALUES (?, ?)");
    $stmt->bind_param("ss", $author, $message_content);
    if ($stmt->execute()) {
        // Message added successfully, send SNS notification
        try {
            $result = $snsClient->publish([
                'TopicArn' => 'arn:aws:sns:us-east-1:573598993687:FamilyMessageNotifications', // Replace with your SNS Topic ARN
                'Message'  => "New message from $author: $message_content",
                'Subject'  => 'New Message on Family App',
            ]);
            $message = "Message added successfully! Notification sent.";
        } catch (AwsException $e) {
            // Output error message if fails
            $message = "Message added, but failed to send notification: " . $e->getMessage();
        }
    } else {
        $error = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve all messages
$result = $conn->query("SELECT * FROM messages ORDER BY timestamp DESC");

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Family Messages</title>
    <!-- Include Bootstrap CSS from CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS Styles -->
    <style>
        body {
            padding-top: 70px;
        }
        .message-board {
            max-width: 800px;
            margin: auto;
        }
        .message-card {
            margin-bottom: 20px;
        }
        .message-form {
            margin-top: 40px;
        }
        .navbar-custom {
            background-color: #6f42c1;
        }
        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #fff;
        }
        footer {
            margin-top: 40px;
            padding: 20px 0;
            background-color: #f8f9fa;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <a class="navbar-brand" href="index.php">Family App</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"     aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" #navbarNav>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="dreams.php">Dreams</a></li>
                <li class="nav-item active"><a class="nav-link" href="messages.php">Messages</a></li>
            </ul>
        </div>
    </nav>

    <div class="container message-board">
        <h1 class="text-center">Family Message Board</h1>
        <?php if ($message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Display the list of messages -->
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="card message-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row["author"]); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?php echo date('F j, Y, g:i a', strtotime($row["timestamp"])); ?></h6>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($row["message_content"])); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No messages found.</p>
        <?php endif; ?>

        <!-- Form to add a new message -->
        <div class="message-form">
            <h2>Add a Message</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="author">Your Name:</label>
                    <input type="text" class="form-control" id="author" name="author" required>
                </div>
                <div class="form-group">
                    <label for="message_content">Your Message:</label>
                    <textarea class="form-control" id="message_content" name="message_content" rows="5" required></textarea>
                </div>
                <button type="submit" name="add_message" class="btn btn-primary">Add Message</button>
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

