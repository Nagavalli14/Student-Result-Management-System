<?php
session_start(); // Start the session

// Include the database connection
include 'db_connection.php';

// Check if the student is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login_check.php');
    exit();
}

// Retrieve the student's username from session
$username = $_SESSION['username'];

// Retrieve exam_name from POST request
$exam_name = isset($_POST['examName']) ? $_POST['examName'] : '';

// Fetch results from the database for the selected exam
$sql = "SELECT * FROM excel_data WHERE name = ? AND exam_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $exam_name);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
if ($result->num_rows > 0) {
    $results = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $message = "No results found for the selected exam.";
}

$stmt->close();
$conn->close();

function getSGPAPointsFromGrade($grade) {
    switch (strtoupper($grade)) {
        case 'S': return 9.5;
        case 'A': return 8.5;
        case 'B': return 7.5;
        case 'C': return 6.0;
        case 'D': return 5.0;
        case 'E': return 3.0; // E grade considered as passing but low
        case 'F': return 0.0; // F is fail
        default: return 0.0;
    }
}

$totalSGPA = 0;
$subjectCount = 0;
$hasFail = false;

foreach ($results as $result) {
    if (strtoupper($result['grade']) == 'F') {
        $hasFail = true;
        break;
    }
    $totalSGPA += getSGPAPointsFromGrade($result['grade']);
    $subjectCount++;
}

$sgpa = (!$hasFail && $subjectCount > 0) ? round($totalSGPA / $subjectCount, 2) : '---';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        .top-right {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .bottom-left {
            position: fixed;
            bottom: 10px;
            left: 10px;
        }
        .bottom-right {
            position: fixed;
            bottom: 10px;
            right: 10px;
        }
        .grade-distribution {
            max-width: 300px; /* Set a max width for the Grade Distribution */
            font-size: 12px; /* Reduce font size */
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Logout button at top-right corner -->
        <a href="logout.php" class="btn btn-danger top-right">Logout</a>

        <!-- Result Card Header -->
        <h2 class="text-center text-uppercase">SHRI VISHNU ENGINEERING COLLEGE FOR WOMEN</h2>
        <h4 class="text-center">Name: <?php echo htmlspecialchars($username); ?></h4>

        <?php if (isset($message)): ?>
            <div class="alert alert-danger">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($results)): ?>
            <div class="row mt-4">
                <!-- Left side: Results Table -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Results for <?php echo htmlspecialchars($exam_name); ?></h5>
                            <table class="table table-bordered" id="resultTable">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $result): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($result['subject']); ?></td>
                                            <td><?php echo htmlspecialchars($result['grade']); ?></td>
                                            <td>
                                                <?php
                                                if (strtoupper($result['grade']) == 'F') {
                                                    echo "Fail";
                                                } else {
                                                    echo "Pass";
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <h5 class="mt-4">SGPA: <?php echo $sgpa; ?></h5>
                        </div>
                    </div>
                </div>

                <!-- Right side: Grade Distribution Table -->
                <div class="col-md-4">
                    <div class="card grade-distribution">
                        <div class="card-body">
                            <h5 class="card-title">Grade Distribution</h5>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Marks Range</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Marks > 90</td>
                                        <td>S</td>
                                    </tr>
                                    <tr>
                                        <td>Marks > 80</td>
                                        <td>A</td>
                                    </tr>
                                    <tr>
                                        <td>Marks > 70</td>
                                        <td>B</td>
                                    </tr>
                                    <tr>
                                        <td>Marks > 60</td>
                                        <td>C</td>
                                    </tr>
                                    <tr>
                                        <td>Marks > 50</td>
                                        <td>D</td>
                                    </tr>
                                    <tr>
                                        <td>Marks > 40</td>
                                        <td>E</td>
                                    </tr>
                                    <tr>
                                        <td>Marks <= 40</td>
                                        <td>F</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Download Button at bottom-right corner -->
            <button id="downloadPDF" class="btn btn-success bottom-right">Download Result as PDF</button>

            <script>
                document.getElementById('downloadPDF').addEventListener('click', function() {
                    const { jsPDF } = window.jspdf;

                    const pdf = new jsPDF();
                    pdf.text("SHRI VISHNU ENGINEERING COLLEGE FOR WOMEN", 10, 10);
                    pdf.text(`Name: <?php echo htmlspecialchars($username); ?>`, 10, 20);
                    pdf.text(`Exam Name: <?php echo htmlspecialchars($exam_name); ?>`, 10, 30);

                    const tableData = <?php echo json_encode($results); ?>.map(result => [
                        result.subject,
                        result.grade,
                        result.grade.toUpperCase() === 'F' ? 'Fail' : 'Pass'
                    ]);

                    pdf.autoTable({
                        head: [['Subject', 'Grade', 'Status']],
                        body: tableData,
                        startY: 40
                    });

                    pdf.text(`SGPA: <?php echo $sgpa; ?>`, 10, pdf.lastAutoTable.finalY + 10);

                    // Adding the Grade Distribution Table to PDF
                    const gradeDistribution = [
                        ['Marks > 90', 'S'],
                        ['Marks > 80', 'A'],
                        ['Marks > 70', 'B'],
                        ['Marks > 60', 'C'],
                        ['Marks > 50', 'D'],
                        ['Marks > 40', 'E'],
                        ['Marks <= 40', 'F']
                    ];

                    pdf.autoTable({
                        head: [['Marks Range', 'Grade']],
                        body: gradeDistribution,
                        startY: pdf.lastAutoTable.finalY + 10
                    });

                    pdf.save(`Result_Card_<?php echo htmlspecialchars($exam_name); ?>.pdf`);
                });
            </script>
        <?php endif; ?>

        <!-- Back to Home button at bottom-left corner -->
        <a href="index.html" class="btn btn-primary bottom-left">Back to Home</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
