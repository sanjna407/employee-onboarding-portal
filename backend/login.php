<?php

session_start();

require_once "config/database.php";


// ========================================
// CHECK FORM SUBMISSION
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../frontend/login.html");
    exit();
}


// ========================================
// GET FORM DATA
// ========================================

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";


// ========================================
// VALIDATE INPUT
// ========================================

if (empty($email) || empty($password)) {
    die("Please enter email and password.");
}


// ========================================
// FIND USER
// ========================================

$query = "SELECT id, name, email, password, role
          FROM users
          WHERE email = ?";

$stmt = $conn->prepare($query);

$stmt->bind_param("s", $email);

$stmt->execute();

$result = $stmt->get_result();


// ========================================
// CHECK USER
// ========================================

if ($result->num_rows === 0) {

    $stmt->close();
    $conn->close();

    die("Invalid email or password.");

}


$user = $result->fetch_assoc();


// ========================================
// VERIFY PASSWORD
// ========================================

if (!password_verify($password, $user["password"])) {

    $stmt->close();
    $conn->close();

    die("Invalid email or password.");

}


// ========================================
// CREATE SESSION
// ========================================

$_SESSION["user_id"] = $user["id"];
$_SESSION["name"] = $user["name"];
$_SESSION["email"] = $user["email"];
$_SESSION["role"] = $user["role"];


// ========================================
// CLOSE DATABASE
// ========================================

$stmt->close();
$conn->close();


// ========================================
// REDIRECT BASED ON ROLE
// ========================================

if ($user["role"] === "admin") {

    header("Location: ../frontend/admin-dashboard.php");

} else {

    header("Location: ../frontend/dashboard.php");

}

exit();

?>