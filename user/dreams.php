<?php

// Connect to the RDS MySQL database
$servername = "family-app-db.cblynykvsyaq.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "mypassword";
$dbname = "family";

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for form submission to add a new dream
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_dream'])) {
    $dream = $conn->real_escape_string($_POST["dream"]);
    $stmt = $conn->prepare("INSERT INTO dreams (dream) VALUES (?)");
    $stmt->bind_param("s", $dream);
    $stmt->execute();
    $stmt->close();
    $message = "Dream added successfully!";
}

// Retrieve all dreams
$result = $conn->query("SELECT * FROM dreams ORDER BY id DESC");

$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Family Dreams</title>
    <link rel="stylesheet" href="css/style.css">
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
        <h1>Leahy Family Dreams</h1>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Display the list of dreams -->
        <?php if ($result && $result->num_rows > 0): ?>
            <ul>
                <?php while($row = $result->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($row["dream"]); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No dreams found.</p>
        <?php endif; ?>

        <!-- Form to add a new dream -->
        <h2>Add a Dream</h2>
        <form method="POST" action="">
            <label for="dream">Your Dream:</label>
            <input type="text" id="dream" name="dream" required>
            <button type="submit" name="add_dream">Add Dream</button>
        </form>
    </div>
</body>
</html>