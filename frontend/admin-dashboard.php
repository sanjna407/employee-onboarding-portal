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
// CHECK ADMIN ROLE
// ========================================

if ($_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit();
}


// ========================================
// DATABASE CONNECTION
// ========================================

require_once "../backend/config/database.php";


// ========================================
// FETCH ALL EMPLOYEES
// ========================================

$query = "SELECT id, name, email, role
          FROM users
          WHERE role = 'employee'
          ORDER BY id ASC";

$result = $conn->query($query);

$employees = [];

while ($employee = $result->fetch_assoc()) {
    $employees[] = $employee;
}


// ========================================
// FETCH TASKS AND DOCUMENTS
// ========================================

foreach ($employees as &$employee) {

    $employee_id = $employee["id"];


    // ====================================
    // FETCH TASK INFORMATION
    // ====================================

    $taskQuery = "SELECT
                    COUNT(*) AS total_tasks,
                    SUM(status = 'completed') AS completed_tasks
                  FROM tasks
                  WHERE employee_id = ?";

    $taskStmt = $conn->prepare($taskQuery);

    $taskStmt->bind_param("i", $employee_id);

    $taskStmt->execute();

    $taskResult = $taskStmt->get_result();

    $taskData = $taskResult->fetch_assoc();


    $employee["total_tasks"] =
        (int)($taskData["total_tasks"] ?? 0);

    $employee["completed_tasks"] =
        (int)($taskData["completed_tasks"] ?? 0);


    // ====================================
    // CALCULATE PROGRESS
    // ====================================

    if ($employee["total_tasks"] > 0) {

        $employee["progress"] = round(
            ($employee["completed_tasks"] /
            $employee["total_tasks"]) * 100
        );

    } else {

        $employee["progress"] = 0;

    }


    $taskStmt->close();


    // ====================================
    // FETCH DOCUMENTS
    // ====================================

    $documentQuery = "SELECT
                        id,
                        document_type,
                        file_name,
                        file_path,
                        status,
                        uploaded_at
                      FROM documents
                      WHERE employee_id = ?
                      ORDER BY uploaded_at DESC";

    $documentStmt = $conn->prepare($documentQuery);

    $documentStmt->bind_param("i", $employee_id);

    $documentStmt->execute();

    $documentResult = $documentStmt->get_result();

    $employee["documents"] = [];


    while ($document = $documentResult->fetch_assoc()) {

        $employee["documents"][] = $document;

    }


    $documentStmt->close();
}

unset($employee);


// ========================================
// CLOSE DATABASE
// ========================================

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

    <title>Admin Dashboard</title>

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
        Employee Onboarding Portal - Admin
    </div>

    <nav>

        <a href="admin-dashboard.php">
            Dashboard
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>


<!-- ========================================
     ADMIN DASHBOARD
     ======================================== -->

<section class="dashboard">


    <!-- DASHBOARD HEADER -->

    <div class="dashboard-header">

        <h1>
            Admin Dashboard 👨‍💼
        </h1>

        <p>
            Manage employees and monitor onboarding progress.
        </p>

    </div>


    <!-- ====================================
         EMPLOYEE LIST
         ==================================== -->

    <?php if (count($employees) > 0): ?>


        <?php foreach ($employees as $employee): ?>


            <!-- EMPLOYEE CARD -->

            <div class="admin-employee-card">


                <!-- ==================================
                     EMPLOYEE INFORMATION
                     ================================== -->

                <div class="admin-employee-header">

                    <div>

                        <h2>
                            <?php
                            echo htmlspecialchars(
                                $employee["name"]
                            );
                            ?>
                        </h2>

                        <p>

                            <strong>
                                Email:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $employee["email"]
                            );
                            ?>

                        </p>

                    </div>


                    <span class="employee-badge">
                        Employee
                    </span>

                </div>


                <!-- ==================================
                     ONBOARDING PROGRESS
                     ================================== -->

                <div class="admin-progress-section">

                    <h3>
                        Onboarding Progress
                    </h3>


                    <div class="progress-bar">

                        <div
                            class="progress-fill"
                            style="width: <?php
                            echo $employee["progress"];
                            ?>%;"
                        >

                            <?php
                            echo $employee["progress"];
                            ?>%

                        </div>

                    </div>


                    <p>

                        <?php
                        echo $employee["completed_tasks"];
                        ?>

                        of

                        <?php
                        echo $employee["total_tasks"];
                        ?>

                        tasks completed

                    </p>

                </div>


                <!-- ==================================
                     DOCUMENTS
                     ================================== -->

                <div class="admin-documents">

                    <h3>
                        Uploaded Documents
                    </h3>


                    <?php if (
                        count($employee["documents"]) > 0
                    ): ?>


                        <?php foreach (
                            $employee["documents"]
                            as $document
                        ): ?>


                            <!-- DOCUMENT ITEM -->

                            <div class="admin-document-item">


                                <!-- DOCUMENT INFORMATION -->

                                <div class="document-info">

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


                                    <!-- VIEW DOCUMENT -->

                                    <a
                                        href="../backend/<?php
                                        echo htmlspecialchars(
                                            $document["file_path"]
                                        );
                                        ?>"
                                        target="_blank"
                                        class="view-document-btn"
                                    >
                                        View Document
                                    </a>

                                </div>


                                <!-- DOCUMENT ACTIONS -->

                                <div class="admin-document-actions">


                                    <!-- STATUS -->

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


                                    <!-- APPROVE / REJECT -->

                                    <?php if (
                                        $document["status"] === "pending"
                                    ): ?>


                                        <!-- APPROVE -->

                                        <form
                                            action="../backend/update_document.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="document_id"
                                                value="<?php
                                                echo $document["id"];
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="approved"
                                            >

                                            <button
                                                type="submit"
                                                class="approve-btn"
                                            >
                                                Approve
                                            </button>

                                        </form>


                                        <!-- REJECT -->

                                        <form
                                            action="../backend/update_document.php"
                                            method="POST"
                                        >

                                            <input
                                                type="hidden"
                                                name="document_id"
                                                value="<?php
                                                echo $document["id"];
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="status"
                                                value="rejected"
                                            >

                                            <button
                                                type="submit"
                                                class="reject-btn"
                                            >
                                                Reject
                                            </button>

                                        </form>


                                    <?php endif; ?>


                                </div>


                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <p class="no-documents">
                            No documents uploaded yet.
                        </p>


                    <?php endif; ?>


                </div>


            </div>


        <?php endforeach; ?>


    <?php else: ?>


        <!-- NO EMPLOYEES -->

        <div class="dashboard-card">

            <h3>
                No Employees Found
            </h3>

            <p>
                There are currently no registered employees.
            </p>

        </div>


    <?php endif; ?>


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