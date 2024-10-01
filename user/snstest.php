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
    'region'  => 'us-east-1', // Replace with your AWS region
    // Credentials are automatically picked up from environment variables or IAM roles
]);

// Initialize variables
$message = '';
$error = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $result = $snsClient->publish([
            'TopicArn' => 'arn:aws:sns:us-east-1:573598993687:FamilyDreamNotifications', 
            'Message'  => 'Test notification from snstest.php',
            'Subject'  => 'SNS Test Notification',
        ]);
        $message = 'Notification sent successfully!';
    } catch (AwsException $e) {
        $error = 'Error sending notification: ' . $e->getAwsErrorMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SNS Test Page</title>
</head>
<body>
    <h1>SNS Test Page</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <button type="submit" name="send_notification">Send SNS Notification</button>
    </form>
</body>
</html>
