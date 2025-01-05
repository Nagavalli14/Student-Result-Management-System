<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();


$host = "localhost";
$user = "root";
$password = "";
$db = "resultsystem";

$data = mysqli_connect($host, $user, $password, $db);

if ($data === false) {
    die("Connection error: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($data, $_POST['username']);
    $pass = mysqli_real_escape_string($data, $_POST['password']);

    if (empty($name) || empty($pass)) {
        die("Username or password cannot be empty.");
    }

    $sql = "SELECT * FROM user WHERE username='$name' AND password='$pass'";
    $result = mysqli_query($data, $sql);

    if (!$result) {
        die("Query error: " . mysqli_error($data));
    }

    $row = mysqli_fetch_array($result);

    if (!$row) {
        die("No matching user found. Check your username and password.");
    }

    if (!isset($row["usertype"])) {
        die("The 'usertype' key does not exist in the database result.");
    }

    if ($row["usertype"] == "student") {
        $_SESSION['username'] = $name;
        $_SESSION['usertype'] = "student";
        header("Location: studenthome.html");
        exit();
    } elseif ($row["usertype"] == "admin") {
        $_SESSION['username'] = $name;
        $_SESSION['usertype'] = "admin";
        header("Location: adminhome.html");
        exit();
    } else {
        $_SESSION['loginMessage'] = "Invalid user type.";
        header("Location: login.html");
        exit();
    }
}
?>