<?php
include 'db_connection.php'; // Include the database connection

$message = ""; // Initialize the message variable

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['resultFile'])) {
    $fileName = $_FILES['resultFile']['name'];
    $fileTmpName = $_FILES['resultFile']['tmp_name'];
    $fileSize = $_FILES['resultFile']['size'];
    $fileError = $_FILES['resultFile']['error'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Allowed file types
    $allowedTypes = ['xls', 'xlsx'];

    // Validate file type
    if (in_array($fileExtension, $allowedTypes)) {
        if ($fileError === 0) {
            if ($fileSize <= 5 * 1024 * 1024) { // Max file size: 5MB
                $uploadDirectory = "uploads/";
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0777, true); // Create directory if it doesn't exist
                }
                $uploadPath = $uploadDirectory . basename($fileName);

                // Move the uploaded file
                if (move_uploaded_file($fileTmpName, $uploadPath)) {
                    $message = "<div class='alert alert-success'>File uploaded successfully: $fileName</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Failed to move the uploaded file.</div>";
                }
            } else {
                $message = "<div class='alert alert-danger'>File size exceeds the limit of 5MB.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Error during file upload. Error code: $fileError</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Invalid file type. Only .xls and .xlsx files are allowed.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Results File</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Upload Results File</h1>
        <hr>

        <!-- Display Notification Message -->
        <?php if ($message): ?>
            <div><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="resultFile" class="form-label">Select Excel File</label>
                <input type="file" class="form-control" id="resultFile" name="resultFile" accept=".xlsx, .xls" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Upload File</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>