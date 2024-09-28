<?php

// Connect to the RDS MySQL database
$servername = "family-app-db.cblynykvsyaq.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "mypassword";
$dbname = "family";

require_once 'aws-config.php';

use Aws\S3\Exception\S3Exception;

// AWS S3 configuration
$bucketName = 'family-app-objects';

// Handle photo deletion
if (isset($_GET['delete_key'])) {
    $key = $_GET['delete_key'];

    try {
        $s3Client->deleteObject([
            'Bucket' => $bucketName,
            'Key'    => $key,
        ]);
        $message = "Photo deleted successfully!";
    } catch (S3Exception $e) {
        $message = "Error deleting photo: " . $e->getMessage();
    }
}

// Retrieve list of photos
try {
    $objects = $s3Client->listObjectsV2([
        'Bucket' => $bucketName,
        'Prefix' => 'photos/',
    ]);
} catch (S3Exception $e) {
    $message = "Error retrieving photos: " . $e->getMessage();
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Admin Photo Management</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Menu -->
        <nav>
            <a href="index.php">Home</a>
            <a href="dreams.php">Manage Dreams</a>
            <a href="photos.php">Manage Photos</a>
        </nav>

        <h1>Manage Photo Album</h1>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Display the photo gallery with delete options -->
        <div class="gallery">
            <?php
            if (isset($objects['Contents'])):
                foreach ($objects['Contents'] as $object):
                    $key = $object['Key'];
                    $photoUrl = $s3Client->getObjectUrl($bucketName, $key);
            ?>
                <div class="photo">
                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Photo">
                    <a href="?delete_key=<?php echo urlencode($key); ?>" onclick="return confirm('Are you sure?');" class="delete-button">Delete</a>
                </div>
            <?php
                endforeach;
            else:
                echo "<p>No photos found.</p>";
            endif;
            ?>
        </div>
    </div>
</body>
</html>
