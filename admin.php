<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Replace with your MySQL password
$dbname = "resultsystem"; // Replace with your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = ""; // Initialize message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve form data (exam details)
    $exam_name = $_POST['examName'];
    $exam_type = $_POST['examType'];
    $exam_month = $_POST['examMonth'];
    $exam_year = $_POST['examYear'];

    // Validate exam name
    if (empty($exam_name)) {
        $message = "<div class='alert alert-danger'>Exam Name is required.</div>";
    } else {
        // Insert exam details into the exams table
        $stmt = $conn->prepare("INSERT INTO exams (exam_name, exam_type, month, year) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $exam_name, $exam_type, $exam_month, $exam_year);
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Exam details submitted successfully.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }

        // Handle the file upload (CSV)
        if (isset($_FILES['resultFile']) && $_FILES['resultFile']['error'] == 0) {
            $fileName = $_FILES['resultFile']['name'];
            $fileTmpName = $_FILES['resultFile']['tmp_name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Allowed file types
            $allowedTypes = ['csv'];

            if (in_array($fileExtension, $allowedTypes)) {
                // Open the file for reading
                $csvFile = fopen($fileTmpName, 'r');
                $headerSkipped = false;
                $currentName = "";

                while (($row = fgetcsv($csvFile)) !== FALSE) {
                    // Skip the header row
                    if (!$headerSkipped) {
                        $headerSkipped = true;
                        continue;
                    }

                    // Process each row
                    $name = !empty($row[0]) ? $row[0] : $currentName; // Use the name if provided, else use the last known name
                    $currentName = $name; // Update the current name
                    $subject = $row[1];  // Subject name
                    $grade = $row[2];    // Grade obtained

                    // Validate and insert data into excel_data table
                    if (!empty($name) && !empty($subject) && !empty($grade)) {
                        $stmt = $conn->prepare("INSERT INTO excel_data (name, grade, subject, exam_name) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $name, $grade, $subject, $exam_name);
                        $stmt->execute();
                    }
                }

                fclose($csvFile);
                $message .= "<div class='alert alert-success'>CSV data uploaded successfully.</div>";
            } else {
                $message .= "<div class='alert alert-danger'>Invalid file type. Only .csv files are allowed.</div>";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Home - Student Result Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Admin Panel - Student Result Management System</h1>
        <hr>

        <!-- Logout Button -->
        <div class="text-end mb-4">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Display Notification Message -->
        <?php if ($message != ""): ?>
            <div class='alert alert-info'>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Form for Exam Details -->
        <div class="card mb-4 shadow">
            <div class="card-header bg-primary text-white">
                <h3>Enter Exam Details</h3>
            </div>
            <div class="card-body">
                <form id="examForm" method="POST" enctype="multipart/form-data" action="admin.php">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="examName" class="form-label">Name of Examination</label>
                            <input type="text" class="form-control" id="examName" name="examName" placeholder="e.g., Semester 1" required>
                        </div>
                        <div class="col-md-6">
                            <label for="examType" class="form-label">Type of Examination</label>
                            <select class="form-select" id="examType" name="examType" required>
                                <option value="Regular">Regular</option>
                                <option value="Backlog">Backlog</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="examMonth" class="form-label">Month</label>
                            <input type="text" class="form-control" id="examMonth" name="examMonth" placeholder="e.g., January" required>
                        </div>
                        <div class="col-md-6">
                            <label for="examYear" class="form-label">Year</label>
                            <input type="number" class="form-control" id="examYear" name="examYear" placeholder="e.g., 2024" required>
                        </div>
                    </div>

                    <!-- Upload CSV File -->
                    <div class="mb-3">
                        <label for="resultFile" class="form-label">Select CSV File</label>
                        <input type="file" class="form-control" id="resultFile" name="resultFile" accept=".csv" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Submit Exam Details and Upload File</button>
                </form>
            </div>
        </div>

        <!-- Back to Home Button -->
        <div class="text-center mt-4">
            <a href="index.html" class="btn btn-primary">Back to Home</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
