<?php

session_start();


// ========================================
// CHECK ADMIN LOGIN
// ========================================

if (!isset($_SESSION["user_id"])) {
    header("Location: ../frontend/login.html");
    exit();
}


// ========================================
// CHECK ADMIN ROLE
// ========================================

if ($_SESSION["role"] !== "admin") {
    header("Location: ../frontend/dashboard.php");
    exit();
}


// ========================================
// DATABASE CONNECTION
// ========================================

require_once "config/database.php";


// ========================================
// GET FORM DATA
// ========================================

$document_id = $_POST["document_id"] ?? 0;
$status = $_POST["status"] ?? "";


// ========================================
// VALIDATE STATUS
// ========================================

$allowed_statuses = ["approved", "rejected"];

if (!in_array($status, $allowed_statuses)) {
    die("Invalid document status.");
}


// ========================================
// UPDATE DOCUMENT
// ========================================

$query = "UPDATE documents
          SET status = ?
          WHERE id = ?";

$stmt = $conn->prepare($query);

$stmt->bind_param(
    "si",
    $status,
    $document_id
);

$stmt->execute();

$stmt->close();

$conn->close();


// ========================================
// RETURN TO ADMIN DASHBOARD
// ========================================

header("Location: ../frontend/admin-dashboard.php");

exit();

?>