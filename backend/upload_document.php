<?php

session_start();

require_once "config/database.php";


// Check if employee is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../frontend/login.html");
    exit();
}

$employee_id = $_SESSION["user_id"];


// Check if file was uploaded
if (!isset($_FILES["document"]) || $_FILES["document"]["error"] !== UPLOAD_ERR_OK) {
    die("Please select a file to upload.");
}


$document_type = trim($_POST["document_type"]);

if (empty($document_type)) {
    die("Please select a document type.");
}


$file = $_FILES["document"];

$file_name = $file["name"];
$file_tmp = $file["tmp_name"];
$file_size = $file["size"];


// Allowed file types
$allowed_types = [
    "pdf",
    "doc",
    "docx",
    "jpg",
    "jpeg",
    "png"
];

$file_extension = strtolower(
    pathinfo($file_name, PATHINFO_EXTENSION)
);


if (!in_array($file_extension, $allowed_types)) {
    die("Invalid file type. Allowed: PDF, DOC, DOCX, JPG, JPEG, PNG.");
}


// Maximum file size: 5 MB
if ($file_size > 5 * 1024 * 1024) {
    die("File is too large. Maximum size is 5 MB.");
}


// Create a unique file name
$new_file_name = uniqid() . "_" . basename($file_name);

$upload_directory = "uploads/";

$file_path = $upload_directory . $new_file_name;


// Move file to uploads folder
if (!move_uploaded_file($file_tmp, $file_path)) {
    die("Failed to upload file.");
}


// Save document information in database
$query = "INSERT INTO documents
          (employee_id, document_type, file_name, file_path, status)
          VALUES (?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($query);

$stmt->bind_param(
    "isss",
    $employee_id,
    $document_type,
    $file_name,
    $file_path
);


if ($stmt->execute()) {

    echo "Document uploaded successfully!";

} else {

    echo "Database error: " . $conn->error;

}


$stmt->close();
$conn->close();

?>