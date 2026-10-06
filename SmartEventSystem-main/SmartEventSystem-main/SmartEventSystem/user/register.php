<?php

session_start();

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Get Logged In User ID
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


/*
|--------------------------------------------------------------------------
| Handle Event Registration
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_id = isset($_POST["event_id"]) ? intval($_POST["event_id"]) : 0;


    /*
    |--------------------------------------------------------------------------
    | Validate Event ID
    |--------------------------------------------------------------------------
    */

    if ($event_id <= 0) {

        $message = "Please select an event.";
        $message_type = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Get Event Details
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT
                id,
                event_name,
                event_date,
                registration_deadline,
                max_participants,
                status
             FROM events
             WHERE id = ?"
        );

        $stmt->bind_param("i", $event_id);
        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 0) {

            $message = "Event not found.";
            $message_type = "error";

        } else {

            $event = $result->fetch_assoc();


            /*
            |--------------------------------------------------------------------------
            | Check Event Status
            |--------------------------------------------------------------------------
            */

            if ($event["status"] === "cancelled") {

                $message = "This event has been cancelled.";
                $message_type = "error";

            } elseif ($event["status"] === "completed") {

                $message = "This event has already been completed.";
                $message_type = "error";

            } elseif (
                !empty($event["registration_deadline"]) &&
                date("Y-m-d") > $event["registration_deadline"]
            ) {

                $message = "Registration deadline for this event has passed.";
                $message_type = "error";

            } else {


                /*
                |--------------------------------------------------------------------------
                | Check Duplicate Registration
                |--------------------------------------------------------------------------
                */

                $check_stmt = $conn->prepare(
                    "SELECT id
                     FROM registrations
                     WHERE user_id = ?
                     AND event_id = ?"
                );

                $check_stmt->bind_param("ii", $user_id, $event_id);
                $check_stmt->execute();

                $check_result = $check_stmt->get_result();


                if ($check_result->num_rows > 0) {

                    $message = "You are already registered for this event.";
                    $message_type = "warning";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | Check Event Capacity
                    |--------------------------------------------------------------------------
                    */

                    $count_stmt = $conn->prepare(
                        "SELECT COUNT(*) AS total_registered
                         FROM registrations
                         WHERE event_id = ?"
                    );

                    $count_stmt->bind_param("i", $event_id);
                    $count_stmt->execute();

                    $count_result = $count_stmt->get_result();
                    $count_data = $count_result->fetch_assoc();

                    $total_registered = intval(
                        $count_data["total_registered"]
                    );

                    $max_participants = intval(
                        $event["max_participants"]
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Check Available Seats
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $max_participants > 0 &&
                        $total_registered >= $max_participants
                    ) {

                        $message = "Sorry, this event is already full.";
                        $message_type = "error";

                    } else {


                        /*
                        |--------------------------------------------------------------------------
                        | Insert Registration
                        |--------------------------------------------------------------------------
                        */

                        $insert_stmt = $conn->prepare(
                            "INSERT INTO registrations
                            (
                                user_id,
                                event_id,
                                registration_date,
                                status
                            )
                            VALUES (?, ?, NOW(), 'registered')"
                        );

                        $insert_stmt->bind_param(
                            "ii",
                            $user_id,
                            $event_id
                        );


                        if ($insert_stmt->execute()) {

                            $message = "Registration successful! You are now registered for " .
                                       $event["event_name"] . ".";

                            $message_type = "success";

                        } else {

                            $message = "Registration failed. Please try again.";
                            $message_type = "error";
                        }


                        $insert_stmt->close();
                    }


                    $count_stmt->close();
                }


                $check_stmt->close();
            }
        }


        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Get Available Events
|--------------------------------------------------------------------------
*/

$events = [];

$stmt = $conn->prepare(
    "SELECT
        e.id,
        e.event_name,
        e.description,
        e.category,
        e.event_date,
        e.start_time,
        e.end_time,
        e.venue,
        e.max_participants,
        e.registration_deadline,
        e.image,
        e.status,
        (
            SELECT COUNT(*)
            FROM registrations r
            WHERE r.event_id = e.id
        ) AS total_registered
     FROM events e
     WHERE e.status IN ('upcoming', 'ongoing')
     ORDER BY e.event_date ASC, e.start_time ASC"
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {
    $events[] = $row;
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

    <title>
        Register for Event - SmartEventSystem
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        /* =========================================================
           SmartEventSystem Logo
        ========================================================= */

        .logo {
            text-decoration: none !important;
        }

        .logo:hover {
            text-decoration: none !important;
        }


        /* =========================================================
           Register Container
        ========================================================= */

        .register-container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }


        /* =========================================================
           Register Header
        ========================================================= */

        .register-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .register-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
            font-size: 16px;
        }


        /* =========================================================
           Message
        ========================================================= */

        .message {
            width: 100%;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-sizing: border-box;
            font-size: 15px;
        }

        .message.success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .message.warning {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
        }


        /* =========================================================
           Event Grid
        ========================================================= */

        .register-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
        }


        /* =========================================================
           Event Card
        ========================================================= */

        .event-card {
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.10);
            transition: 0.3s;

            display: flex;
            flex-direction: column;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }


        /* =========================================================
           Event Image
        ========================================================= */

        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .no-image {
            width: 100%;
            height: 200px;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 15px;
        }


        /* =========================================================
           Event Content
        ========================================================= */

        .event-content {
            padding: 20px;

            display: flex;
            flex-direction: column;

            flex: 1;
        }

        .event-content h2 {
            font-size: 21px;
            margin-bottom: 10px;
        }

        .event-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }


        /* =========================================================
           Event Details
        ========================================================= */

        .event-details {
            margin-top: 15px;
        }

        .event-detail {
            margin-bottom: 8px;
            font-size: 14px;
            color: #444;
        }

        .event-detail strong {
            color: #222;
        }


        /* =========================================================
           Event Status
        ========================================================= */

        .event-status {
            display: inline-block;
            width: fit-content;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            margin-top: 10px;
        }

        .event-status.upcoming {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        .event-status.ongoing {
            background-color: #dcfce7;
            color: #15803d;
        }


        /* =========================================================
           Register Form
        ========================================================= */

        .register-form {
            margin-top: auto;
            padding-top: 25px;
        }


        /* =========================================================
           Register Now Button
        ========================================================= */

        .register-now-button {
            width: 100%;
            padding: 14px 20px;

            background-color: #2563eb;
            color: #ffffff;

            border: none;
            border-radius: 8px;

            font-size: 16px;
            font-weight: 600;

            cursor: pointer;

            transition: 0.3s;
        }

        .register-now-button:hover {
            background-color: #1d4ed8;
            transform: translateY(-2px);
        }


        /* =========================================================
           Back To Dashboard
        ========================================================= */

        .dashboard-button-container {
            display: flex;
            justify-content: center;
            margin-top: 35px;
            margin-bottom: 20px;
        }

        .dashboard-button {
            display: inline-block;

            padding: 13px 30px;

            background-color: #6b7280;
            color: #ffffff;

            border-radius: 8px;

            text-decoration: none;

            font-size: 15px;
            font-weight: 600;

            transition: 0.3s;
        }

        .dashboard-button:hover {
            background-color: #4b5563;
            color: #ffffff;
            text-decoration: none;
            transform: translateY(-2px);
        }


        /* =========================================================
           No Events
        ========================================================= */

        .no-events {
            grid-column: 1 / -1;

            text-align: center;

            padding: 60px 20px;

            background-color: #ffffff;

            border-radius: 12px;

            border: 1px solid #e5e7eb;
        }

        .no-events h2 {
            margin-bottom: 10px;
        }

        .no-events p {
            color: #666;
            margin-bottom: 20px;
        }


        /* =========================================================
           Responsive Design
        ========================================================= */

        @media (max-width: 900px) {

            .register-grid {
                grid-template-columns: repeat(2, 1fr);
            }

        }


        @media (max-width: 600px) {

            .register-grid {
                grid-template-columns: 1fr;
            }

            .register-container {
                width: 95%;
            }

            .register-header h1 {
                font-size: 26px;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     Navigation
========================================================= -->

<nav class="navbar">

    <div class="nav-container">

        <a
            href="../dashboard.php"
            class="logo"
        >
            SmartEventSystem
        </a>


        <div class="navigation">

            <a href="../dashboard.php">
                Dashboard
            </a>

            <a href="register.php">
                Register Event
            </a>

            <a href="myevents.php">
                My Events
            </a>

            <a href="profile.php">
                Profile
            </a>

            <a href="../logout.php">
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================================================
     Main Container
========================================================= -->

<main class="register-container">


    <!-- =====================================================
         Register Header
    ====================================================== -->

    <div class="register-header">

        <h1>
            Register for an Event
        </h1>

        <p>
            Choose an event and register to participate.
        </p>

    </div>


    <!-- =====================================================
         Message
    ====================================================== -->

    <?php if (!empty($message)): ?>

        <div
            class="message <?php echo htmlspecialchars($message_type); ?>"
        >

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         Event Grid
    ====================================================== -->

    <div class="register-grid">


        <?php if (count($events) > 0): ?>


            <?php foreach ($events as $event): ?>


                <!-- =============================================
                     Event Card
                ============================================== -->

                <div class="event-card">


                    <!-- =========================================
                         Event Image
                    ========================================== -->

                    <?php if (!empty($event["image"])): ?>

                        <img
                            src="../uploads/events/<?php echo htmlspecialchars($event["image"]); ?>"
                            alt="<?php echo htmlspecialchars($event["event_name"]); ?>"
                            class="event-image"
                        >

                    <?php else: ?>

                        <div class="no-image">

                            No Image Available

                        </div>

                    <?php endif; ?>


                    <!-- =========================================
                         Event Content
                    ========================================== -->

                    <div class="event-content">


                        <!-- =====================================
                             Event Name
                        ====================================== -->

                        <h2>

                            <?php
                            echo htmlspecialchars(
                                $event["event_name"]
                            );
                            ?>

                        </h2>


                        <!-- =====================================
                             Description
                        ====================================== -->

                        <?php if (!empty($event["description"])): ?>

                            <p class="event-description">

                                <?php
                                echo htmlspecialchars(
                                    $event["description"]
                                );
                                ?>

                            </p>

                        <?php endif; ?>


                        <!-- =====================================
                             Event Details
                        ====================================== -->

                        <div class="event-details">


                            <div class="event-detail">

                                <strong>
                                    Category:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $event["category"] ?? "General"
                                );
                                ?>

                            </div>


                            <div class="event-detail">

                                <strong>
                                    Date:
                                </strong>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($event["event_date"])
                                );
                                ?>

                            </div>


                            <div class="event-detail">

                                <strong>
                                    Time:
                                </strong>

                                <?php

                                echo date(
                                    "h:i A",
                                    strtotime($event["start_time"])
                                );


                                if (!empty($event["end_time"])) {

                                    echo " - ";

                                    echo date(
                                        "h:i A",
                                        strtotime($event["end_time"])
                                    );
                                }

                                ?>

                            </div>


                            <div class="event-detail">

                                <strong>
                                    Venue:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $event["venue"]
                                );
                                ?>

                            </div>


                            <div class="event-detail">

                                <strong>
                                    Participants:
                                </strong>

                                <?php

                                echo intval(
                                    $event["total_registered"]
                                );

                                echo " / ";

                                echo intval(
                                    $event["max_participants"]
                                );

                                ?>

                            </div>


                            <?php if (!empty($event["registration_deadline"])): ?>

                                <div class="event-detail">

                                    <strong>
                                        Registration Deadline:
                                    </strong>

                                    <?php
                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $event["registration_deadline"]
                                        )
                                    );
                                    ?>

                                </div>

                            <?php endif; ?>


                        </div>


                        <!-- =====================================
                             Event Status
                        ====================================== -->

                        <span
                            class="event-status <?php echo htmlspecialchars($event["status"]); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $event["status"]
                            );
                            ?>

                        </span>


                        <!-- =====================================
                             Register Now Button
                        ====================================== -->

                        <form
                            method="POST"
                            action="register.php"
                            class="register-form"
                        >

                            <input
                                type="hidden"
                                name="event_id"
                                value="<?php echo intval($event["id"]); ?>"
                            >


                            <button
                                type="submit"
                                class="register-now-button"
                            >
                                Register Now
                            </button>

                        </form>


                    </div>

                </div>


            <?php endforeach; ?>


        <?php else: ?>


            <!-- =================================================
                 No Events Available
            ================================================== -->

            <div class="no-events">

                <h2>
                    No Events Available
                </h2>

                <p>
                    There are currently no upcoming events available for registration.
                </p>

            </div>


        <?php endif; ?>


    </div>


    <!-- =====================================================
         Back To Dashboard Button
    ====================================================== -->

    <div class="dashboard-button-container">

        <a
            href="../dashboard.php"
            class="dashboard-button"
        >
            ← Back to Dashboard
        </a>

    </div>


</main>


<!-- =========================================================
     Footer
========================================================= -->

<footer class="footer">

    <p>

        &copy;
        <?php echo date("Y"); ?>

        SmartEventSystem.
        All Rights Reserved.

    </p>

</footer>


<!-- =========================================================
     JavaScript
========================================================= -->

<script src="../js/angular.min.js"></script>

<script src="../js/app.js"></script>


</body>

</html>