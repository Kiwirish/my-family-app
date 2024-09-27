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

// Handle dream deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM dreams WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $message = "Dream deleted successfully!";
    } else {
        $message = "Error deleting dream: " . $stmt->error;
    }

    $stmt->close();
}

// Retrieve the dreams
$result = $conn->query("SELECT * FROM dreams ORDER BY id DESC");

$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Admin - Manage Family Dreams</title>
    <style>
        /* Same styles as before, you can adjust as needed */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
        }
        .container {
            width: 600px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px 30px 30px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        .message {
            background-color: #e0ffe0;
            border: 1px solid #b0ffb0;
            padding: 10px;
            margin-bottom: 20px;
            color: #008000;
            border-radius: 5px;
        }
        ul {
            list-style-type: none;
            padding-left: 0;
        }
        li {
            background-color: #f0f0f0;
            margin: 5px 0;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            color: #555;
            position: relative;
        }
        .delete-button {
            position: absolute;
            right: 15px;
            top: 15px;
            background-color: #ff4d4d;
            color: #fff;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-button:hover {
            background-color: #e60000;
        }
        .dream-text {
            margin-right: 100px; /* Adjust to prevent overlap with delete button */
        }
        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }
        .nav-links a {
            margin: 0 10px;
            color: #4285F4;
            text-decoration: none;
            font-size: 16px;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin TEST - Manage Family Dreams</h1>

        <div class="nav-links">
            <a href="index.php">User Page</a>
            <!-- Add other navigation links if needed -->
        </div>

        <?php if (isset($message)): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <ul>
                <?php while($row = $result->fetch_assoc()): ?>
                    <li>
                        <span class="dream-text"><?php echo htmlspecialchars($row["dream"]); ?></span>
                        <form method="GET" action="" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="delete-button" onclick="return confirm('Are you sure you want to delete this dream?');">Delete</button>
                        </form>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No dreams found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

