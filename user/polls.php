<?php
// Start the session if you plan to use session variables in the future
// session_start();

// Include Composer's autoloader for AWS SDK
require '/var/www/html/vendor/autoload.php';

// use Aws\Sns\SnsClient;
// use Aws\Exception\AwsException;

// AWS SDK Configuration (if using SNS for notifications)
// $snsClient = new SnsClient([
//     'version' => 'latest',
//     'region'  => 'us-east-1',
//     // Credentials are automatically picked up from environment variables or EC2 IAM roles
// ]);

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

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form is for creating a new poll
    if (isset($_POST['create_poll'])) {
        $question = $conn->real_escape_string($_POST["question"]);
        $options = $_POST["options"]; // Array of options
        $created_by = $conn->real_escape_string($_POST["created_by"]);
        $expires_at = !empty($_POST["expires_at"]) ? $conn->real_escape_string($_POST["expires_at"]) : null;

        // Insert into polls table
        $stmt = $conn->prepare("INSERT INTO polls (question, created_by, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $question, $created_by, $expires_at);
        if ($stmt->execute()) {
            $poll_id = $stmt->insert_id;
            $stmt->close();

            // Insert options into poll_options table
            $stmt = $conn->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
            foreach ($options as $option_text) {
                // Ensure non-empty options
                if (!empty(trim($option_text))) {
                    $option_text = $conn->real_escape_string($option_text);
                    $stmt->bind_param("is", $poll_id, $option_text);
                    $stmt->execute();
                }
            }
            $stmt->close();

            // Send SNS notification (optional)
            // try {
            //     $result = $snsClient->publish([
            //         'TopicArn' => 'arn:aws:sns:us-east-1:573598993687:FamilyPollNotifications', // Replace with your SNS Topic ARN
            //         'Message'  => "A new poll has been created: \"$question\"",
            //         'Subject'  => 'New Family Poll Created',
            //     ]);
            //     $message = "Poll created successfully! Notification sent.";
            // } catch (AwsException $e) {
            //     $message = "Poll created, but failed to send notification: " . $e->getMessage();
            // }
        } else {
            $error = "Error creating poll: " . $stmt->error;
        }
    }

    // Check if the form is for voting on a poll
    if (isset($_POST['vote'])) {
        $poll_id = intval($_POST["poll_id"]);
        $option_id = intval($_POST["option_id"]);
        $voter_name = $conn->real_escape_string($_POST["voter_name"]);

        // Optional: Check if the voter has already voted on this poll
        /*
        $check_vote = $conn->prepare("SELECT id FROM poll_votes WHERE poll_id = ? AND voter_name = ?");
        $check_vote->bind_param("is", $poll_id, $voter_name);
        $check_vote->execute();
        $check_vote->store_result();
        if ($check_vote->num_rows > 0) {
            $error = "You have already voted on this poll.";
        } else {
            // Insert the vote
            $check_vote->close();
        }
        */

        // Insert the vote into poll_votes
        $stmt = $conn->prepare("INSERT INTO poll_votes (poll_id, option_id, voter_name) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $poll_id, $option_id, $voter_name);
        if ($stmt->execute()) {
            $stmt->close();

            // Optional: Send SNS notification about the new vote
            // try {
            //     $result = $snsClient->publish([
            //         'TopicArn' => 'arn:aws:sns:us-east-1:573598993687:FamilyPollNotifications', // Replace with your SNS Topic ARN
            //         'Message'  => "$voter_name voted on poll ID $poll_id.",
            //         'Subject'  => 'New Vote Casted',
            //     ]);
            //     $message = "Your vote has been recorded! Notification sent.";
            // } catch (AwsException $e) {
            //     $message = "Your vote has been recorded, but failed to send notification: " . $e->getMessage();
            // }
        } else {
            $error = "Error recording your vote: " . $stmt->error;
        }
    }
}

// Retrieve active polls (not expired)
$current_datetime = date("Y-m-d H:i:s");
$polls_result = $conn->query("SELECT * FROM polls WHERE expires_at IS NULL OR expires_at > '$current_datetime' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Family Polls</title>
    <!-- Include Bootstrap CSS from CDN -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation Menu -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <a class="navbar-brand" href="index.php">Leahy's App</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <!-- Dynamically set active class based on current page -->
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="dreams.php">Dreams</a></li>
                <li class="nav-item active"><a class="nav-link" href="polls.php">Polls</a></li>
                <li class="nav-item"><a class="nav-link" href="messages.php">Messages</a></li>
                <!-- Add other links as needed -->
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Page Content -->
        <h1>Welcome to the Leahy Family Polls</h1>
        <p class="text-center">Participate in family polls or create your own to engage with family members.</p>

        <!-- Display Success or Error Messages -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Active Polls Section -->
        <h2>Active Polls</h2>
        <?php if ($polls_result && $polls_result->num_rows > 0): ?>
            <?php while($poll = $polls_result->fetch_assoc()): ?>
                <div class="card message-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($poll["question"]); ?></h5>
                        <p class="card-text">Created by: <?php echo htmlspecialchars($poll["created_by"]); ?></p>
                        <p class="card-text">Expires at: <?php echo $poll["expires_at"] ? date('F j, Y, g:i a', strtotime($poll["expires_at"])) : 'Never'; ?></p>
                        <form method="POST" action="polls.php">
                            <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
                            <div class="form-group">
                                <label for="option_id_<?php echo $poll['id']; ?>">Choose an option:</label>
                                <?php
                                // Fetch options for this poll
                                $poll_id = $poll['id'];
                                $options_result = $conn->query("SELECT * FROM poll_options WHERE poll_id = $poll_id");
                                while($option = $options_result->fetch_assoc()):
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="option_id" id="option<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" required>
                                        <label class="form-check-label" for="option<?php echo $option['id']; ?>">
                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                        </label>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="form-group">
                                <label for="voter_name_<?php echo $poll['id']; ?>">Your Name:</label>
                                <input type="text" class="form-control" id="voter_name_<?php echo $poll['id']; ?>" name="voter_name" required>
                            </div>
                            <button type="submit" name="vote" class="btn btn-primary">Vote</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No active polls at the moment.</p>
        <?php endif; ?>

        <!-- Create a New Poll Section -->
        <h2>Create a New Poll</h2>
        <form method="POST" action="polls.php">
            <div class="form-group">
                <label for="question">Poll Question:</label>
                <input type="text" class="form-control" id="question" name="question" placeholder="Enter your poll question" required>
            </div>
            <div class="form-group">
                <label for="options">Options:</label>
                <div id="options-container">
                    <input type="text" class="form-control mb-2" name="options[]" placeholder="Option 1" required>
                    <input type="text" class="form-control mb-2" name="options[]" placeholder="Option 2" required>
                </div>
                <button type="button" class="btn btn-secondary mt-2" id="add-option">Add Another Option</button>
            </div>
            <div class="form-group">
                <label for="created_by">Your Name:</label>
                <input type="text" class="form-control" id="created_by" name="created_by" placeholder="Enter your name" required>
            </div>
            <div class="form-group">
                <label for="expires_at">Expiration Date (optional):</label>
                <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
            </div>
            <button type="submit" name="create_poll" class="btn btn-success">Create Poll</button>
        </form>
    </div>

    <!-- Footer -->
    <footer>
        &copy; <?php echo date("Y"); ?> Leahy's App
    </footer>

    <!-- Include Bootstrap JS and dependencies (jQuery and Popper.js) from CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Custom JavaScript to Add More Poll Options -->
    <script>
        document.getElementById('add-option').addEventListener('click', function() {
            var container = document.getElementById('options-container');
            var inputCount = container.getElementsByTagName('input').length + 1;
            var input = document.createElement('input');
            input.type = 'text';
            input.name = 'options[]';
            input.className = 'form-control mb-2';
            input.placeholder = 'Option ' + inputCount;
            container.appendChild(input);
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
