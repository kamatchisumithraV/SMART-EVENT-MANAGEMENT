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
| Check Admin Role
|--------------------------------------------------------------------------
*/

if ($_SESSION["role"] !== "admin") {

    header("Location: ../dashboard.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Get Event ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: view_events.php");
    exit();

}


$event_id = (int)$_GET["id"];


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$message = "";

$message_type = "";


/*
|--------------------------------------------------------------------------
| Fetch Event Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        event_name,
        image
     FROM events
     WHERE id = ?"
);


if ($stmt === false) {

    die("Database error: " . $conn->error);

}


$stmt->bind_param(
    "i",
    $event_id
);


$stmt->execute();


$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Check Event Exists
|--------------------------------------------------------------------------
*/

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: view_events.php");
    exit();

}


$event = $result->fetch_assoc();


$stmt->close();


$event_name = $event["event_name"];

$current_image = $event["image"];


/*
|--------------------------------------------------------------------------
| Handle Delete Confirmation
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | Delete Event
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare(
        "DELETE FROM events
         WHERE id = ?"
    );


    if ($stmt === false) {

        $message =
            "Database error: "
            . $conn->error;

        $message_type =
            "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Bind Parameters
        |--------------------------------------------------------------------------
        */

        $stmt->bind_param(
            "i",
            $event_id
        );


        /*
        |--------------------------------------------------------------------------
        | Execute Delete
        |--------------------------------------------------------------------------
        */

        if ($stmt->execute()) {


            /*
            |--------------------------------------------------------------------------
            | Delete Event Image
            |--------------------------------------------------------------------------
            */

            if (!empty($current_image)) {


                $image_path =
                    "../uploads/events/"
                    . $current_image;


                if (
                    file_exists(
                        $image_path
                    )
                ) {

                    unlink(
                        $image_path
                    );

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Close Statement
            |--------------------------------------------------------------------------
            */

            $stmt->close();


            /*
            |--------------------------------------------------------------------------
            | Redirect After Successful Delete
            |--------------------------------------------------------------------------
            */

            header("Location: view_events.php?message=deleted");
            exit();


        } else {


            $message =
                "Failed to delete event: "
                . $stmt->error;

            $message_type =
                "danger";


            $stmt->close();

        }

    }

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
        Delete Event - Smart Event System
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .delete-card {

            max-width: 600px;

            margin: 40px auto;

            text-align: center;

        }


        .warning-icon {

            font-size: 50px;

            margin-bottom: 15px;

        }


        .delete-title {

            color: #dc2626;

            margin-bottom: 15px;

        }


        .delete-message {

            color: #4b5563;

            line-height: 1.6;

            margin-bottom: 25px;

        }


        .event-name {

            font-weight: bold;

            color: #111827;

        }


        .warning-box {

            background-color: #fef2f2;

            border: 1px solid #fecaca;

            color: #991b1b;

            padding: 15px;

            border-radius: 6px;

            margin-bottom: 25px;

        }


        .delete-buttons {

            display: flex;

            justify-content: center;

            gap: 12px;

            flex-wrap: wrap;

        }


        .btn-danger {

            background-color: #dc2626;

            color: white;

            border: none;

            cursor: pointer;

        }


        .btn-danger:hover {

            background-color: #b91c1c;

        }


        .btn-secondary {

            background-color: #6b7280;

            color: white;

        }


        .btn-secondary:hover {

            background-color: #4b5563;

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


            <a href="view_events.php">
                Events
            </a>


            <a href="../reports/report.php">
                Reports
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
            Delete Event
        </h1>

        <p>
            Remove an event from the system.
        </p>

    </div>



    <!-- =====================================================
         Message
    ====================================================== -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         Delete Confirmation
    ====================================================== -->

    <div class="card delete-card">


        <div class="warning-icon">

            ⚠️

        </div>


        <h2 class="delete-title">

            Confirm Event Deletion

        </h2>


        <p class="delete-message">

            Are you sure you want to delete the event

            <span class="event-name">

                <?php

                echo htmlspecialchars(
                    $event_name
                );

                ?>

            </span>

            ?

        </p>


        <div class="warning-box">

            <strong>
                Warning:
            </strong>

            This action cannot be undone.

            The event and its associated image will be
            permanently removed.

        </div>



        <!-- Delete Form -->

        <form
            method="POST"
            action="delete_event.php?id=<?php echo $event_id; ?>"
        >


            <div class="delete-buttons">


                <button
                    type="submit"
                    class="btn btn-danger"
                >

                    Delete Event

                </button>


                <a
                    href="view_events.php"
                    class="btn btn-secondary"
                >

                    Cancel

                </a>


            </div>


        </form>


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

$conn->close();

?>


</body>

</html>