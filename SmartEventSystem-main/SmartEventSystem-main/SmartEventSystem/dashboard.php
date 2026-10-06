<?php

session_start();

require_once "config/db.php";

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["name"];
$user_role = $_SESSION["role"];

/*
|--------------------------------------------------------------------------
| Get Dashboard Statistics
|--------------------------------------------------------------------------
*/

// Total events
$total_events = 0;

$result = $conn->query(
    "SELECT COUNT(*) AS total FROM events"
);

if ($result) {
    $row = $result->fetch_assoc();
    $total_events = $row["total"];
}

// Upcoming events
$upcoming_events = 0;

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM events
     WHERE event_date >= CURDATE()"
);

$stmt->execute();

$result = $stmt->get_result();

if ($result) {
    $row = $result->fetch_assoc();
    $upcoming_events = $row["total"];
}

$stmt->close();

// User's registrations
$my_registrations = 0;

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM registrations
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result) {
    $row = $result->fetch_assoc();
    $my_registrations = $row["total"];
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Smart Event System</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>

<body>

<!-- =========================================================
     Navigation Bar
========================================================= -->

<nav class="navbar">

    <div class="navbar-container">

        <div class="logo">
            Smart Event System
        </div>

        <div class="nav-links">

            <a href="dashboard.php">
                Dashboard
            </a>

            <?php if ($user_role === "admin"): ?>

                <a href="admin/view_events.php">
                    Events
                </a>

                <a href="reports/report.php">
                    Reports
                </a>

            <?php else: ?>

                <a href="user/myevents.php">
                    My Events
                </a>

                <a href="user/profile.php">
                    Profile
                </a>

            <?php endif; ?>

            <a href="logout.php">
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================================================
     Dashboard
========================================================= -->

<div class="container">

    <div class="page-title">

        <h1>
            Welcome, <?php echo htmlspecialchars($user_name); ?>!
        </h1>

        <p>
            <?php
            if ($user_role === "admin") {
                echo "Administrator Dashboard";
            } else {
                echo "User Dashboard";
            }
            ?>
        </p>

    </div>


    <!-- =====================================================
         Statistics
    ====================================================== -->

    <div class="card-grid">

        <div class="card">

            <h3>
                Total Events
            </h3>

            <p style="font-size: 32px; font-weight: bold;">
                <?php echo $total_events; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                Upcoming Events
            </h3>

            <p style="font-size: 32px; font-weight: bold;">
                <?php echo $upcoming_events; ?>
            </p>

        </div>


        <div class="card">

            <h3>
                My Registrations
            </h3>

            <p style="font-size: 32px; font-weight: bold;">
                <?php echo $my_registrations; ?>
            </p>

        </div>

    </div>


    <!-- =====================================================
         Admin Dashboard
    ====================================================== -->

    <?php if ($user_role === "admin"): ?>

        <h2 style="margin-top: 40px;">
            Administration
        </h2>

        <div class="card-grid">

            <div class="card">

                <h3>
                    Manage Events
                </h3>

                <p>
                    Add, edit, delete and view events.
                </p>

                <a
                    href="admin/view_events.php"
                    class="btn btn-primary"
                >
                    Manage Events
                </a>

            </div>


            <div class="card">

                <h3>
                    Add New Event
                </h3>

                <p>
                    Create a new event in the system.
                </p>

                <a
                    href="admin/add_event.php"
                    class="btn btn-success"
                >
                    Add Event
                </a>

            </div>


            <div class="card">

                <h3>
                    Reports
                </h3>

                <p>
                    View event registration and attendance reports.
                </p>

                <a
                    href="reports/report.php"
                    class="btn btn-warning"
                >
                    View Reports
                </a>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         Normal User Dashboard
    ====================================================== -->

    <?php if ($user_role === "user"): ?>

        <h2 style="margin-top: 40px;">
            User Options
        </h2>

        <div class="card-grid">

            <div class="card">

                <h3>
                    Browse Events
                </h3>

                <p>
                    View available events and register for them.
                </p>

                <a
                    href="user/register.php"
                    class="btn btn-primary"
                >
                    View Events
                </a>

            </div>


            <div class="card">

                <h3>
                    My Events
                </h3>

                <p>
                    View the events you have registered for.
                </p>

                <a
                    href="user/myevents.php"
                    class="btn btn-success"
                >
                    My Events
                </a>

            </div>


            <div class="card">

                <h3>
                    My Profile
                </h3>

                <p>
                    View and manage your profile information.
                </p>

                <a
                    href="user/profile.php"
                    class="btn btn-warning"
                >
                    Profile
                </a>

            </div>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         Account Information
    ====================================================== -->

    <div class="card" style="margin-top: 40px;">

        <h3>
            Account Information
        </h3>

        <p>
            <strong>Name:</strong>
            <?php echo htmlspecialchars($user_name); ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?php echo htmlspecialchars($_SESSION["email"]); ?>
        </p>

        <p>
            <strong>Role:</strong>
            <?php echo htmlspecialchars(ucfirst($user_role)); ?>
        </p>

    </div>

</div>


<!-- =========================================================
     Footer
========================================================= -->

<footer>

    <p>
        &copy; <?php echo date("Y"); ?>
        Smart Event System. All Rights Reserved.
    </p>

</footer>

</body>

</html>