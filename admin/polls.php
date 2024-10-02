<?php
// Include Composer's autoloader for AWS SDK
require '/var/www/html/vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

// AWS SDK Configuration
$snsClient = new SnsClient([
    'version' => 'latest',
    'region'  => 'us-east-1', // Replace with your AWS region
    // Credentials are automatically picked up from environment variables, IAM roles, or AWS credentials file
]);

// Database connection details
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

// Initialize variables for messages
$message = '';
$error = '';

// Handle posting poll results
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_result'])) {
    $poll_id = intval($_POST["poll_id"]);
    $poll_result = $conn->real_escape_string($_POST["poll_result"]);

    // Insert poll result into poll_results table
    $stmt = $conn->prepare("INSERT INTO poll_results (poll_id, result_text) VALUES (?, ?)");
    $stmt->bind_param("is", $poll_id, $poll_result);
    if ($stmt->execute()) {
        $message = "Poll result posted successfully!";

        // Retrieve the poll question for the notification
        $stmt->close();
        $stmt = $conn->prepare("SELECT question FROM polls WHERE id = ?");
        $stmt->bind_param("i", $poll_id);
        $stmt->execute();
        $stmt->bind_result($poll_question);
        $stmt->fetch();
        $stmt->close();

        // Prepare the notification message
        $notification_message = "A new result has been posted for the poll: \"$poll_question\".\n\nResult: $poll_result";

        try {
            $result = $snsClient->publish([
                'TopicArn' => 'arn:aws:sns:us-east-1:573598993687:PollResultNotification', // Replace with your SNS Topic ARN
                'Message'  => $notification_message,
                'Subject'  => 'New Family Poll Result Posted',
            ]);
            $message .= " Notifications have been sent.";
        } catch (AwsException $e) {
            // Output error message if fails
            $error = "Poll result posted, but failed to send notification: " . $e->getMessage();
        }
    } else {
        $error = "Error posting poll result: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve all polls
$polls_result = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");

// Function to get poll results
function getPollResult($conn, $poll_id) {
    $stmt = $conn->prepare("SELECT result_text FROM poll_results WHERE poll_id = ?");
    $stmt->bind_param("i", $poll_id);
    $stmt->execute();
    $stmt->bind_result($result_text);
    $stmt->fetch();
    $stmt->close();
    return $result_text;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Manage Polls</title>
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
        <h1>Manage Family Polls</h1>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Display All Polls with Votes and Results -->
        <?php if ($polls_result && $polls_result->num_rows > 0): ?>
            <?php while($poll = $polls_result->fetch_assoc()): ?>
                <div class="card message-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($poll["question"]); ?></h5>
                        <p class="card-text">Created by: <?php echo htmlspecialchars($poll["created_by"]); ?></p>
                        <p class="card-text">Expires at: <?php echo $poll["expires_at"] ? date('F j, Y, g:i a', strtotime($poll["expires_at"])) : 'Never'; ?></p>
                        
                        <!-- Display Poll Votes -->
                        <h6>Votes:</h6>
                        <?php
                        $poll_id = $poll['id'];
                        // Retrieve options with vote counts
                        $options_result = $conn->query("SELECT po.id, po.option_text, COUNT(pv.id) as vote_count 
                                                       FROM poll_options po
                                                       LEFT JOIN poll_votes pv ON po.id = pv.option_id
                                                       WHERE po.poll_id = $poll_id
                                                       GROUP BY po.id, po.option_text
                                                       ORDER BY po.id ASC");
                        ?>
                        <ul class="list-group mb-3">
                            <?php while($option = $options_result->fetch_assoc()): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($option["option_text"]); ?>
                                    <span class="badge badge-primary badge-pill"><?php echo $option["vote_count"]; ?></span>
                                </li>
                            <?php endwhile; ?>
                        </ul>

                        <!-- Display Poll Result if exists -->
                        <?php
                        $poll_result = getPollResult($conn, $poll_id);
                        if ($poll_result):
                        ?>
                            <h6>Poll Result:</h6>
                            <p><?php echo nl2br(htmlspecialchars($poll_result)); ?></p>
                        <?php else: ?>
                            <!-- Form to Post Poll Result -->
                            <form method="POST" action="polls.php">
                                <input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>">
                                <div class="form-group">
                                    <label for="poll_result_<?php echo $poll_id; ?>">Post Poll Result:</label>
                                    <textarea class="form-control" id="poll_result_<?php echo $poll_id; ?>" name="poll_result" rows="3" placeholder="Enter the poll result here..." required></textarea>
                                </div>
                                <button type="submit" name="post_result" class="btn btn-success">Post Result</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No polls found.</p>
        <?php endif; ?>
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