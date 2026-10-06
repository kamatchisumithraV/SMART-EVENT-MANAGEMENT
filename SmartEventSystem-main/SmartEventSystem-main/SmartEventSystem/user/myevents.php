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


$user_id = (int)$_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$message = "";

$message_type = "";


/*
|--------------------------------------------------------------------------
| Fetch Registered Events
|--------------------------------------------------------------------------
*/

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
        e.image,
        e.status
     FROM registrations r
     INNER JOIN events e
        ON r.event_id = e.id
     WHERE r.user_id = ?
     ORDER BY e.event_date ASC, e.start_time ASC"
);


if ($stmt === false) {

    die("Database error: " . $conn->error);

}


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();


$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Check Registered Events
|--------------------------------------------------------------------------
*/

if ($result->num_rows === 0) {

    $message =
        "You have not registered for any events yet.";

    $message_type =
        "info";

}

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
        My Events - Smart Event System
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .event-table-container {

            width: 100%;

            overflow-x: auto;

        }


        .event-table {

            width: 100%;

            border-collapse: collapse;

            margin-top: 20px;

        }


        .event-table th {

            background-color: #1f2937;

            color: white;

            padding: 14px;

            text-align: left;

        }


        .event-table td {

            padding: 14px;

            border-bottom: 1px solid #ddd;

            vertical-align: middle;

        }


        .event-table tr:hover {

            background-color: #f9fafb;

        }


        .event-image {

            width: 80px;

            height: 60px;

            object-fit: cover;

            border-radius: 6px;

        }


        .no-image {

            width: 80px;

            height: 60px;

            display: flex;

            align-items: center;

            justify-content: center;

            background-color: #e5e7eb;

            color: #6b7280;

            border-radius: 6px;

            font-size: 12px;

        }


        .event-status {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

            text-transform: capitalize;

        }


        .status-upcoming {

            background-color: #dbeafe;

            color: #1d4ed8;

        }


        .status-ongoing {

            background-color: #dcfce7;

            color: #15803d;

        }


        .status-completed {

            background-color: #e5e7eb;

            color: #374151;

        }


        .status-cancelled {

            background-color: #fee2e2;

            color: #b91c1c;

        }


        .empty-events {

            text-align: center;

            padding: 50px 20px;

        }


        .empty-events h2 {

            margin-bottom: 10px;

        }


        .empty-events p {

            color: #6b7280;

            margin-bottom: 25px;

        }


        @media (max-width: 768px) {

            .event-table {

                min-width: 900px;

            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     Navigation
========================================================= -->

<nav class="navbar">

    <div class="navbar-container">


        <div class="logo">

            Smart Event System

        </div>


        <div class="nav-links">

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

<div class="container">


    <!-- Page Title -->

    <div class="page-title">

        <h1>
            My Events
        </h1>

        <p>
            View the events you have registered for.
        </p>

    </div>



    <!-- =====================================================
         Message
    ====================================================== -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         Registered Events
    ====================================================== -->

    <div class="card">


        <?php if ($result->num_rows > 0): ?>


            <div class="event-table-container">


                <table class="event-table">


                    <thead>

                        <tr>

                            <th>
                                S.No
                            </th>


                            <th>
                                Image
                            </th>


                            <th>
                                Event Name
                            </th>


                            <th>
                                Category
                            </th>


                            <th>
                                Date
                            </th>


                            <th>
                                Time
                            </th>


                            <th>
                                Venue
                            </th>


                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php

                        $serial_no = 1;

                        ?>


                        <?php while ($event = $result->fetch_assoc()): ?>


                            <tr>


                                <!-- Serial Number -->

                                <td>

                                    <?php
                                    echo $serial_no;
                                    ?>

                                </td>



                                <!-- Event Image -->

                                <td>


                                    <?php if (!empty($event["image"])): ?>

                                        <img
                                            src="../uploads/events/<?php echo htmlspecialchars($event["image"]); ?>"
                                            alt="Event Image"
                                            class="event-image"
                                        >

                                    <?php else: ?>

                                        <div class="no-image">

                                            No Image

                                        </div>

                                    <?php endif; ?>


                                </td>



                                <!-- Event Name -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $event["event_name"]
                                        );
                                        ?>

                                    </strong>

                                </td>



                                <!-- Category -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $event["category"]
                                    );

                                    ?>

                                </td>



                                <!-- Event Date -->

                                <td>

                                    <?php

                                    echo date(
                                        "d-m-Y",
                                        strtotime($event["event_date"])
                                    );

                                    ?>

                                </td>



                                <!-- Event Time -->

                                <td>

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

                                </td>



                                <!-- Venue -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $event["venue"]
                                    );

                                    ?>

                                </td>



                                <!-- Status -->

                                <td>


                                    <span
                                        class="event-status status-<?php echo htmlspecialchars($event["status"]); ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $event["status"]
                                        );

                                        ?>

                                    </span>


                                </td>


                            </tr>


                        <?php

                            $serial_no++;

                        ?>


                        <?php endwhile; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <!-- =================================================
                 No Registered Events
            ================================================== -->

            <div class="empty-events">


                <h2>
                    No Registered Events
                </h2>


                <p>

                    You have not registered for any events yet.

                </p>


                <a
                    href="register.php"
                    class="btn btn-primary"
                >
                    Register for an Event
                </a>


            </div>


        <?php endif; ?>


    </div>


</div>



<!-- =========================================================
     Footer
========================================================= -->

<footer>

    <p>

        &copy; <?php echo date("Y"); ?>

        Smart Event System.

        All Rights Reserved.

    </p>

</footer>



<?php

$stmt->close();

$conn->close();

?>


</body>

</html>