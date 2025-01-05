<?php
session_start(); // Start the session

include 'db_connection.php'; // Include the database connection

// Check if the student is logged in
if (!isset($_SESSION['student_username'])) {
    header("Location: login_check.php"); // Redirect to login page if not logged in
    exit();
}

// Retrieve the student's username from the session
$student_username = $_SESSION['student_username'];

// Fetch the available exam names and types
$sql = "SELECT DISTINCT exam_name, exam_type FROM excel_data WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_username); // Bind the student username to the query
$stmt->execute();
$exam_result = $stmt->get_result();

$exams = [];
if ($exam_result->num_rows > 0) {
    while ($row = $exam_result->fetch_assoc()) {
        $exams[] = $row;
    }
} else {
    $message = "No exams found for your username.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Student Home</h1>
        <div class="text-center mb-4">
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <!-- Display message if no exams are found -->
        <?php if (isset($message)): ?>
            <div class="alert alert-danger">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Exam Selection Form -->
        <form action="retrive.php" method="GET">
            <div class="mb-3">
                <label for="examName" class="form-label">Select Exam Name</label>
                <select class="form-select" id="examName" name="examName" required>
                    <option value="">Select an Exam</option>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?php echo htmlspecialchars($exam['exam_name']); ?>"><?php echo htmlspecialchars($exam['exam_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="examType" class="form-label">Select Exam Type</label>
                <select class="form-select" id="examType" name="examType" required>
                    <option value="">Select Exam Type</option>
                    <option value="Regular">Regular</option>
                    <option value="Backlog">Backlog</option>
                </select>
            </div>
            <a href="retrive.php" class="btn btn-primary">View Results</a>
            
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
