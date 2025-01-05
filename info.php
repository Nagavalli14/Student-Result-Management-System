<?php
$conn = mysqli_connect('localhost', 'root', '', 'resultsystem');
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
echo 'Connected successfully!';
?>
