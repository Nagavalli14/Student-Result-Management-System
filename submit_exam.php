<?php
// Include the database connection
include 'db_connection.php';

$message = ""; // Initialize the message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $exam_name = $_POST['examName'];
    $exam_type = $_POST['examType'];
    $exam_month = $_POST['examMonth'];
    $exam_year = $_POST['examYear'];

    // Insert data into the database
    $sql = "INSERT INTO exams (exam_name, exam_type, month, year) 
            VALUES ('$exam_name', '$exam_type', '$exam_month', '$exam_year')";

    if ($conn->query($sql) === TRUE) {
        $message = "<div class='alert alert-success'>Exam details submitted successfully.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Exam Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Submit Exam Details</h1>
        <hr>

        <!-- Display Notification Message -->
        <?php if ($message): ?>
            <div><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Exam Submission Form -->
        
        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="adminhome.html" class="btn btn-primary">Back to Admin Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
