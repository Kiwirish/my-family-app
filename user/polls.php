<?php
require '/var/www/html/vendor/autoload.php'; // Include AWS SDK if using SNS

// Use AWS SDK classes if integrating SNS
//use Aws\Sns\SnsClient;
//use Aws\Exception\AwsException;

// AWS SDK Configuration (if using SNS)
// $snsClient = new SnsClient([
//     'version' => 'latest',
//     'region'  => 'us-east-1',
// ]);

// Database connection
$servername = "family-app-db.cblynykvsyaq.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "mypassword";
$dbname = "family";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for form submission to create a new poll
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_poll'])) {
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
            $option_text = $conn->real_escape_string($option_text);
            $stmt->bind_param("is", $poll_id, $option_text);
            $stmt->execute();
        }
        $stmt->close();

        // Send SNS notification (optional)
        // try {
        //     $result = $snsClient->publish([
        //         'TopicArn' => 'arn:aws:sns:us-east-1:YOUR_AWS_ACCOUNT_ID:FamilyPollNotifications',
        //         'Message'  => "A new poll has been created: $question",
        //         'Subject'  => 'New Family Poll',
        //     ]);
        //     $message = "Poll created successfully! Notification sent.";
        // } catch (AwsException $e) {
        //     $message = "Poll created, but failed to send notification: " . $e->getMessage();
        // }
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Retrieve active polls
$polls_result = $conn->query("SELECT * FROM polls WHERE expires_at IS NULL OR expires_at > NOW() ORDER BY created_at DESC");

// Close the database connection when done
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Family Polls</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Custom CSS Styles -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Include your navigation bar here -->
    <!-- ... -->

    <div class="container">
        <h1 class="text-center">Family Polls</h1>
        <?php if (isset($message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Display Active Polls -->
        <?php if ($polls_result && $polls_result->num_rows > 0): ?>
            <?php while($poll = $polls_result->fetch_assoc()): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($poll["question"]); ?></h5>
                        <p class="card-text">Created by: <?php echo htmlspecialchars($poll["created_by"]); ?></p>
                        <form method="POST" action="vote.php">
                            <input type="hidden" name="poll_id" value="<?php echo $poll['id']; ?>">
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
                            <div class="form-group mt-3">
                                <label for="voter_name">Your Name:</label>
                                <input type="text" class="form-control" id="voter_name" name="voter_name" required>
                            </div>
                            <button type="submit" name="vote" class="btn btn-primary">Vote</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No active polls at the moment.</p>
        <?php endif; ?>

        <!-- Form to Create a New Poll -->
        <div class="mt-5">
            <h2>Create a New Poll</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="question">Poll Question:</label>
                    <input type="text" class="form-control" id="question" name="question" required>
                </div>
                <div class="form-group">
                    <label for="options">Options:</label>
                    <div id="options-container">
                        <input type="text" class="form-control mb-2" name="options[]" placeholder="Option 1" required>
                        <input type="text" class="form-control mb-2" name="options[]" placeholder="Option 2" required>
                    </div>
                    <button type="button" class="btn btn-secondary" id="add-option">Add Another Option</button>
                </div>
                <div class="form-group">
                    <label for="created_by">Your Name:</label>
                    <input type="text" class="form-control" id="created_by" name="created_by" required>
                </div>
                <div class="form-group">
                    <label for="expires_at">Expiration Date (optional):</label>
                    <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                </div>
                <button type="submit" name="create_poll" class="btn btn-success">Create Poll</button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <!-- ... -->

    <!-- Include Bootstrap JS and dependencies (jQuery and Popper.js) from CDN -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script>
        // JavaScript to add more option fields
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
    <!-- Include Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
