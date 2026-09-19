<?php

require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Check if fields are empty
    if (empty($name) || empty($email) || empty($password)) {
        die("All fields are required.");
    }

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Please enter a valid email address.");
    }

    // Check if email already exists
    $checkQuery = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($checkQuery);

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("Email already registered.");
    }

    $stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $insertQuery = "INSERT INTO users (name, email, password, role)
                    VALUES (?, ?, ?, 'employee')";

    $stmt = $conn->prepare($insertQuery);

    $stmt->bind_param(
        "sss",
        $name,
        $email,
        $hashedPassword
    );

    if ($stmt->execute()) {

        echo "Registration successful!";

    } else {

        echo "Registration failed: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

?>