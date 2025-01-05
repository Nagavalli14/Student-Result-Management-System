<?php
// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if examName is set
    if (isset($_POST['examName'])) {
        $examName = $_POST['examName'];
        echo "Exam Name: " . $examName;  // Debugging line to confirm examName

        // Process the uploaded file
        if (isset($_FILES['resultFile']) && $_FILES['resultFile']['error'] == 0) {
            $fileName = $_FILES['resultFile']['name'];
            $fileTmpName = $_FILES['resultFile']['tmp_name'];
            $fileSize = $_FILES['resultFile']['size'];
            $fileError = $_FILES['resultFile']['error'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            // Allowed file types
            $allowedTypes = ['xls', 'xlsx', 'csv'];

            // Check if file type is allowed
            if (in_array($fileExtension, $allowedTypes)) {
                // Handle file upload (for CSV example)
                if ($fileExtension == 'csv') {
                    $csvFile = fopen($fileTmpName, 'r');
                    while (($row = fgetcsv($csvFile)) !== FALSE) {
                        // Assuming CSV has columns: name, score
                        $name = $row[0];
                        $score = (int)$row[1]; // Parse score as integer

                        // Insert the data into the excel_data table with exam name
                        $conn = new mysqli("localhost", "root", "", "resultsystem");
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }

                        $stmt = $conn->prepare("INSERT INTO excel_data (name, score, exam_name) VALUES (?, ?, ?)");
                        $stmt->bind_param("sis", $name, $score, $examName);
                        $stmt->execute();
                        $stmt->close();
                    }
                    fclose($csvFile);

                    echo "Data uploaded successfully.";
                }
            } else {
                echo "Invalid file type.";
            }
        } else {
            echo "File upload failed.";
        }
    } else {
        echo "Exam Name is required.";
    }
}
?>
