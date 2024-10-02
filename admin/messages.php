<?php
// Connect to the RDS MySQL database
$servername = "family-app-db.cblynykvsyaq.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "mypassword";
$dbname = "family";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Handle deletion of a message
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Message deleted successfully!";
    } else {
        $error = "Error deleting message: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve all messages
$result = $conn->query("SELECT * FROM messages ORDER BY timestamp DESC");

$conn->close();
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Admin - Manage Messages</title>
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
        <h1>Manage Family Messages</h1>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="message" style="background-color: #f8d7da; border-color: #f5c6cb; color: #721c24;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Display the list of messages with delete option -->
        <?php if ($result && $result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while($row = $result->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo htmlspecialchars($row["author"]); ?></strong><br>
                            <?php echo nl2br(htmlspecialchars($row["message_content"])); ?><br>
                            <small class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($row["timestamp"])); ?></small>
                        </div>
                        <form method="GET" action="messages.php" class="delete-form">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this message?');">Delete</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No messages found.</p>
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
