<?php

session_start();


// ========================================
// CHECK LOGIN
// ========================================

if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}


// ========================================
// DATABASE CONNECTION
// ========================================

require_once "../backend/config/database.php";


// ========================================
// GET LOGGED-IN USER INFORMATION
// ========================================

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"];
$email = $_SESSION["email"];
$role = $_SESSION["role"];


// ========================================
// FETCH EMPLOYEE TASKS
// ========================================

$query = "SELECT id, title, description, status
          FROM tasks
          WHERE employee_id = ?
          ORDER BY id ASC";

$stmt = $conn->prepare($query);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();


// ========================================
// CALCULATE TASK PROGRESS
// ========================================

$totalTasks = $result->num_rows;

$completedTasks = 0;

$tasks = [];

while ($task = $result->fetch_assoc()) {

    $tasks[] = $task;

    if ($task["status"] === "completed") {
        $completedTasks++;
    }
}


// Calculate percentage

if ($totalTasks > 0) {

    $progress = round(
        ($completedTasks / $totalTasks) * 100
    );

} else {

    $progress = 0;

}


$stmt->close();


// ========================================
// FETCH UPLOADED DOCUMENTS
// ========================================

$documentQuery = "SELECT id, document_type, file_name, status
                  FROM documents
                  WHERE employee_id = ?
                  ORDER BY uploaded_at DESC";

$documentStmt = $conn->prepare($documentQuery);

$documentStmt->bind_param("i", $user_id);

$documentStmt->execute();

$documentResult = $documentStmt->get_result();

$documents = [];

while ($document = $documentResult->fetch_assoc()) {

    $documents[] = $document;

}


$documentStmt->close();

$conn->close();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Employee Dashboard
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


<!-- ========================================
     NAVIGATION
     ======================================== -->

<header>

    <div class="logo">

        Employee Onboarding Portal

    </div>


    <nav>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>



<!-- ========================================
     DASHBOARD
     ======================================== -->

<section class="dashboard">


    <!-- ====================================
         WELCOME
         ==================================== -->

    <div class="dashboard-header">

        <h1>

            Welcome,
            <?php echo htmlspecialchars($name); ?>! 👋

        </h1>


        <p>

            Here's an overview of your
            employee onboarding process.

        </p>

    </div>



    <!-- ====================================
         PROGRESS CARD
         ==================================== -->

    <div class="progress-card">

        <h2>

            Onboarding Progress

        </h2>


        <div class="progress-bar">

            <div
                class="progress-fill"
                style="width: <?php echo $progress; ?>%;"
            >

                <?php echo $progress; ?>%

            </div>

        </div>


        <p>

            <?php echo $completedTasks; ?>

            of

            <?php echo $totalTasks; ?>

            onboarding tasks completed

        </p>

    </div>



    <!-- ====================================
         DASHBOARD CARDS
         ==================================== -->

    <div class="dashboard-cards">


        <!-- ==================================
             PROFILE CARD
             ================================== -->

        <div class="dashboard-card">

            <h3>
                My Profile
            </h3>


            <p>

                <strong>
                    Name:
                </strong>

                <?php echo htmlspecialchars($name); ?>

            </p>


            <p>

                <strong>
                    Email:
                </strong>

                <?php echo htmlspecialchars($email); ?>

            </p>


            <p>

                <strong>
                    Role:
                </strong>

                <?php echo htmlspecialchars($role); ?>

            </p>

        </div>



        <!-- ==================================
             TASKS CARD
             ================================== -->

        <div class="dashboard-card">

            <h3>
                Tasks
            </h3>


            <?php if ($totalTasks > 0): ?>


                <?php foreach ($tasks as $task): ?>


                    <div class="task-item">


                        <div class="task-info">


                            <?php if ($task["status"] === "completed"): ?>

                                <span class="task-completed">
                                    ✓
                                </span>

                            <?php else: ?>

                                <span class="task-pending">
                                    ○
                                </span>

                            <?php endif; ?>


                            <span>

                                <?php
                                echo htmlspecialchars(
                                    $task["title"]
                                );
                                ?>

                            </span>


                        </div>



                        <?php if ($task["status"] !== "completed"): ?>


                            <form
                                action="../backend/update_task.php"
                                method="POST"
                                class="task-form"
                            >


                                <input
                                    type="hidden"
                                    name="task_id"
                                    value="<?php echo $task["id"]; ?>"
                                >


                                <button
                                    type="submit"
                                    class="complete-btn"
                                >

                                    Mark Complete

                                </button>


                            </form>


                        <?php else: ?>


                            <span class="completed-label">

                                Completed

                            </span>


                        <?php endif; ?>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <p>

                    No tasks assigned yet.

                </p>


            <?php endif; ?>


        </div>



        <!-- ==================================
             DOCUMENTS CARD
             ================================== -->

        <div class="dashboard-card">

            <h3>
                Documents
            </h3>


            <!-- Uploaded Documents -->

            <?php if (count($documents) > 0): ?>


                <?php foreach ($documents as $document): ?>


                    <div class="document-item">


                        <div>


                            <strong>

                                <?php
                                echo htmlspecialchars(
                                    $document["document_type"]
                                );
                                ?>

                            </strong>


                            <p class="document-name">

                                <?php
                                echo htmlspecialchars(
                                    $document["file_name"]
                                );
                                ?>

                            </p>


                        </div>



                        <span
                            class="document-status
                            <?php
                            echo strtolower(
                                $document["status"]
                            );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                ucfirst(
                                    $document["status"]
                                )
                            );
                            ?>

                        </span>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <p class="no-documents">

                    No documents uploaded yet.

                </p>


            <?php endif; ?>



            <!-- Separator -->

            <hr>



            <!-- Upload Section -->

            <h4>

                Upload New Document

            </h4>


            <form
                action="../backend/upload_document.php"
                method="POST"
                enctype="multipart/form-data"
                class="document-form"
            >


                <label for="document_type">

                    Document Type

                </label>


                <select
                    id="document_type"
                    name="document_type"
                    required
                >

                    <option value="">

                        Select document

                    </option>


                    <option value="Resume">

                        Resume

                    </option>


                    <option value="Identity Proof">

                        Identity Proof

                    </option>


                    <option value="Bank Details">

                        Bank Details

                    </option>

                </select>



                <label for="document">

                    Choose File

                </label>


                <input
                    type="file"
                    id="document"
                    name="document"
                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                    required
                >


                <p class="file-info">

                    Maximum file size: 5 MB

                </p>


                <button
                    type="submit"
                    class="upload-btn"
                >

                    Upload Document

                </button>


            </form>


        </div>


    </div>


</section>



<!-- ========================================
     FOOTER
     ======================================== -->

<footer>

    <p>

        © 2026 Employee Onboarding Portal

    </p>

</footer>


</body>

</html>