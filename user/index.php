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

// If the form is submitted, insert the dream
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user input for security
    $dream = $conn->real_escape_string($_POST["dream"]);
    $sql = "INSERT INTO dreams (dream) VALUES ('$dream')";

    if ($conn->query($sql) === TRUE) {
        $message = "New dream added successfully!";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// Retrieve the dreams
$result = $conn->query("SELECT * FROM dreams ORDER BY id DESC");

$conn->close();
?>

<!DOCTYPE HTML>
<html>
<head>
    <title>Family Dreams TESTER </title>
    <style>
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
        }
        form {
            margin-top: 30px;
        }
        label {
            font-size: 16px;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px 15px;
            margin: 10px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background-color: #4285F4;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #3071E8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Family Dreams</h1>

        <?php if (isset($message)): ?>
            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <ul>
                <?php while($row = $result->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($row["dream"]); ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No dreams found.</p>
        <?php endif; ?>

        <h2>Add a Dream</h2>
        <form method="POST" action="">
            <label for="dream">Your Dream:</label>
            <input type="text" id="dream" name="dream" required>
            <button type="submit">Add Dream</button>
        </form>
    </div>
</body>
</html>