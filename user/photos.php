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

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['photo'])) {
    $file       = $_FILES['photo']['tmp_name'];
    $fileName   = basename($_FILES['photo']['name']);
    $fileType   = $_FILES['photo']['type'];
    $keyName    = 'photos/' . $fileName;

    try {
        // Upload data to S3
        $result = $s3Client->putObject([
            'Bucket'      => $bucketName,
            'Key'         => $keyName,
            'SourceFile'  => $file,
            'ContentType' => $fileType,
            'ACL'         => 'public-read', // Make file publicly accessible
        ]);
        $message = "Photo uploaded successfully!";
    } catch (S3Exception $e) {
        $message = "Error uploading photo: " . $e->getMessage();
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
    <title>Family Photo Album</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Navigation Menu -->
        <nav>
            <a href="index.php">Home</a>
            <a href="dreams.php">Dreams</a>
            <a href="photos.php">Photos</a>
        </nav>

        <h1>Family Photo Album</h1>
        <?php if (isset($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- Form to upload a photo -->
        <h2>Upload a Photo</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="photo">Choose a photo:</label>
            <input type="file" name="photo" id="photo" accept="image/*" required>
            <button type="submit">Upload Photo</button>
        </form>

        <!-- Display the photo gallery -->
        <h2>Photo Gallery</h2>
        <div class="gallery">
            <?php
            if (isset($objects['Contents'])):
                foreach ($objects['Contents'] as $object):
                    $key = $object['Key'];
                    $photoUrl = $s3Client->getObjectUrl($bucketName, $key);
            ?>
                    <img src="<?php echo htmlspecialchars($photoUrl); ?>" alt="Photo">
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
