<?php

session_start();

require_once "config/database.php";


// Make sure the employee is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../frontend/login.html");
    exit();
}


$user_id = $_SESSION["user_id"];


// Make sure a task ID was submitted
if (!isset($_POST["task_id"])) {
    header("Location: ../frontend/dashboard.php");
    exit();
}

$task_id = intval($_POST["task_id"]);


// Update only the task belonging to the logged-in employee
$query = "UPDATE tasks
          SET status = 'completed'
          WHERE id = ?
          AND employee_id = ?";

$stmt = $conn->prepare($query);

$stmt->bind_param("ii", $task_id, $user_id);

$stmt->execute();

$stmt->close();
$conn->close();


// Return to dashboard
header("Location: ../frontend/dashboard.php");
exit();

?>