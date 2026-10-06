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
| Variables
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "";

$event_name = "";
$description = "";
$category = "";
$event_date = "";
$start_time = "";
$end_time = "";
$venue = "";
$max_participants = "";
$registration_deadline = "";
$status = "upcoming";



/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_name = trim($_POST["event_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $event_date = $_POST["event_date"] ?? "";
    $start_time = $_POST["start_time"] ?? "";
    $end_time = $_POST["end_time"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $max_participants = $_POST["max_participants"] ?? "";
    $registration_deadline = $_POST["registration_deadline"] ?? "";
    $status = $_POST["status"] ?? "upcoming";


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($event_name) ||
        empty($description) ||
        empty($category) ||
        empty($event_date) ||
        empty($start_time) ||
        empty($venue) ||
        empty($max_participants)
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "danger";

    } elseif ((int)$max_participants <= 0) {

        $message = "Maximum participants must be greater than 0.";
        $message_type = "danger";

    } elseif ($event_date < date("Y-m-d")) {

        $message = "Event date cannot be in the past.";
        $message_type = "danger";

    } elseif (
        !empty($end_time) &&
        $end_time <= $start_time
    ) {

        $message = "End time must be later than start time.";
        $message_type = "danger";

    } elseif (
        !empty($registration_deadline) &&
        $registration_deadline > $event_date
    ) {

        $message = "Registration deadline cannot be after the event date.";
        $message_type = "danger";

    } elseif (
        !in_array(
            $status,
            ["upcoming", "ongoing", "completed", "cancelled"]
        )
    ) {

        $message = "Invalid event status.";
        $message_type = "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Handle Image Upload
        |--------------------------------------------------------------------------
        */

        $image_name = null;

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $message = "There was an error uploading the image.";
                $message_type = "danger";

            } else {

                $allowed_extensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "gif",
                    "webp"
                ];

                $file_name = $_FILES["image"]["name"];

                $file_tmp = $_FILES["image"]["tmp_name"];

                $file_size = $_FILES["image"]["size"];

                $file_extension = strtolower(
                    pathinfo(
                        $file_name,
                        PATHINFO_EXTENSION
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | Check File Extension
                |--------------------------------------------------------------------------
                */

                if (
                    !in_array(
                        $file_extension,
                        $allowed_extensions
                    )
                ) {

                    $message =
                        "Only JPG, JPEG, PNG, GIF and WEBP images are allowed.";

                    $message_type = "danger";

                /*
                |--------------------------------------------------------------------------
                | Check File Size
                |--------------------------------------------------------------------------
                */

                } elseif ($file_size > 5 * 1024 * 1024) {

                    $message =
                        "Image size must be less than 5 MB.";

                    $message_type = "danger";

                } else {


                    /*
                    |--------------------------------------------------------------------------
                    | Create Upload Directory
                    |--------------------------------------------------------------------------
                    */

                    $upload_directory =
                        "../uploads/events/";


                    if (
                        !is_dir($upload_directory)
                    ) {

                        mkdir(
                            $upload_directory,
                            0777,
                            true
                        );
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Generate Unique Image Name
                    |--------------------------------------------------------------------------
                    */

                    $image_name =
                        uniqid("event_", true)
                        . "."
                        . $file_extension;


                    $image_path =
                        $upload_directory
                        . $image_name;


                    /*
                    |--------------------------------------------------------------------------
                    | Move Uploaded Image
                    |--------------------------------------------------------------------------
                    */

                    if (
                        !move_uploaded_file(
                            $file_tmp,
                            $image_path
                        )
                    ) {

                        $message =
                            "Failed to save the uploaded image.";

                        $message_type = "danger";

                        $image_name = null;
                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Insert Event
        |--------------------------------------------------------------------------
        */

        if (empty($message)) {

            /*
            | Convert optional empty values to NULL
            */

            $end_time_value =
                !empty($end_time)
                    ? $end_time
                    : null;

            $registration_deadline_value =
                !empty($registration_deadline)
                    ? $registration_deadline
                    : null;


            /*
            |--------------------------------------------------------------------------
            | Prepare SQL
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare(
                "INSERT INTO events
                (
                    event_name,
                    description,
                    category,
                    event_date,
                    start_time,
                    end_time,
                    venue,
                    max_participants,
                    registration_deadline,
                    image,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );


            /*
            |--------------------------------------------------------------------------
            | Check Prepare
            |--------------------------------------------------------------------------
            */

            if ($stmt === false) {

                $message =
                    "Database error: "
                    . $conn->error;

                $message_type = "danger";

            } else {


                /*
                |--------------------------------------------------------------------------
                | Bind Parameters
                |--------------------------------------------------------------------------
                */

                $stmt->bind_param(
                    "ssssssissss",
                    $event_name,
                    $description,
                    $category,
                    $event_date,
                    $start_time,
                    $end_time_value,
                    $venue,
                    $max_participants,
                    $registration_deadline_value,
                    $image_name,
                    $status
                );


                /*
                |--------------------------------------------------------------------------
                | Execute
                |--------------------------------------------------------------------------
                */

                if ($stmt->execute()) {

                    $message =
                        "Event added successfully!";

                    $message_type = "success";


                    /*
                    |--------------------------------------------------------------------------
                    | Clear Form
                    |--------------------------------------------------------------------------
                    */

                    $event_name = "";
                    $description = "";
                    $category = "";
                    $event_date = "";
                    $start_time = "";
                    $end_time = "";
                    $venue = "";
                    $max_participants = "";
                    $registration_deadline = "";
                    $status = "upcoming";

                } else {

                    $message =
                        "Failed to add event: "
                        . $stmt->error;

                    $message_type = "danger";
                }


                $stmt->close();
            }
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
        Add Event - Smart Event System
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | Form Layout
        |--------------------------------------------------------------------------
        */

        .form-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 20px;

        }


        /*
        |--------------------------------------------------------------------------
        | Required Field
        |--------------------------------------------------------------------------
        */

        .required {

            color: #dc2626;

        }


        /*
        |--------------------------------------------------------------------------
        | Image Preview
        |--------------------------------------------------------------------------
        */

        .image-help {

            font-size: 13px;

            color: #6b7280;

            margin-top: 6px;

        }


        /*
        |--------------------------------------------------------------------------
        | Mobile
        |--------------------------------------------------------------------------
        */

        @media (max-width: 768px) {

            .form-row {

                grid-template-columns: 1fr;

            }

        }

    </style>

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


    <!-- =====================================================
         Page Title
    ====================================================== -->

    <div class="page-title">

        <h1>
            Add New Event
        </h1>

        <p>
            Create a new event for users to register.
        </p>

    </div>



    <!-- =====================================================
         Message
    ====================================================== -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-<?php echo htmlspecialchars($message_type); ?>">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         Event Form
    ====================================================== -->

    <div class="card">


        <form
            method="POST"
            action="add_event.php"
            enctype="multipart/form-data"
        >


            <!-- =================================================
                 Event Name
            ================================================== -->

            <div class="form-group">

                <label for="event_name">

                    Event Name

                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    id="event_name"
                    name="event_name"
                    class="form-control"
                    placeholder="Enter event name"
                    value="<?php echo htmlspecialchars($event_name); ?>"
                    required
                >

            </div>



            <!-- =================================================
                 Description
            ================================================== -->

            <div class="form-group">

                <label for="description">

                    Description

                    <span class="required">*</span>

                </label>


                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="5"
                    placeholder="Enter event description"
                    required
                ><?php echo htmlspecialchars($description); ?></textarea>

            </div>



            <!-- =================================================
                 Category + Venue
            ================================================== -->

            <div class="form-row">


                <!-- Category -->

                <div class="form-group">

                    <label for="category">

                        Category

                        <span class="required">*</span>

                    </label>


                    <select
                        id="category"
                        name="category"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>


                        <option
                            value="Technology"
                            <?php
                            echo ($category === "Technology")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Technology
                        </option>


                        <option
                            value="Workshop"
                            <?php
                            echo ($category === "Workshop")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Workshop
                        </option>


                        <option
                            value="Seminar"
                            <?php
                            echo ($category === "Seminar")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Seminar
                        </option>


                        <option
                            value="Conference"
                            <?php
                            echo ($category === "Conference")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Conference
                        </option>


                        <option
                            value="Cultural"
                            <?php
                            echo ($category === "Cultural")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Cultural
                        </option>


                        <option
                            value="Sports"
                            <?php
                            echo ($category === "Sports")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Sports
                        </option>


                        <option
                            value="Other"
                            <?php
                            echo ($category === "Other")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Other
                        </option>

                    </select>

                </div>



                <!-- Venue -->

                <div class="form-group">

                    <label for="venue">

                        Venue

                        <span class="required">*</span>

                    </label>


                    <input
                        type="text"
                        id="venue"
                        name="venue"
                        class="form-control"
                        placeholder="Enter event venue"
                        value="<?php echo htmlspecialchars($venue); ?>"
                        required
                    >

                </div>

            </div>



            <!-- =================================================
                 Event Date + Maximum Participants
            ================================================== -->

            <div class="form-row">


                <!-- Event Date -->

                <div class="form-group">

                    <label for="event_date">

                        Event Date

                        <span class="required">*</span>

                    </label>


                    <input
                        type="date"
                        id="event_date"
                        name="event_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($event_date); ?>"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>



                <!-- Maximum Participants -->

                <div class="form-group">

                    <label for="max_participants">

                        Maximum Participants

                        <span class="required">*</span>

                    </label>


                    <input
                        type="number"
                        id="max_participants"
                        name="max_participants"
                        class="form-control"
                        placeholder="Enter maximum participants"
                        min="1"
                        value="<?php echo htmlspecialchars($max_participants); ?>"
                        required
                    >

                </div>

            </div>



            <!-- =================================================
                 Start Time + End Time
            ================================================== -->

            <div class="form-row">


                <!-- Start Time -->

                <div class="form-group">

                    <label for="start_time">

                        Start Time

                        <span class="required">*</span>

                    </label>


                    <input
                        type="time"
                        id="start_time"
                        name="start_time"
                        class="form-control"
                        value="<?php echo htmlspecialchars($start_time); ?>"
                        required
                    >

                </div>



                <!-- End Time -->

                <div class="form-group">

                    <label for="end_time">

                        End Time

                    </label>


                    <input
                        type="time"
                        id="end_time"
                        name="end_time"
                        class="form-control"
                        value="<?php echo htmlspecialchars($end_time); ?>"
                    >

                </div>

            </div>



            <!-- =================================================
                 Registration Deadline + Status
            ================================================== -->

            <div class="form-row">


                <!-- Registration Deadline -->

                <div class="form-group">

                    <label for="registration_deadline">

                        Registration Deadline

                    </label>


                    <input
                        type="date"
                        id="registration_deadline"
                        name="registration_deadline"
                        class="form-control"
                        value="<?php echo htmlspecialchars($registration_deadline); ?>"
                        min="<?php echo date('Y-m-d'); ?>"
                    >

                </div>



                <!-- Status -->

                <div class="form-group">

                    <label for="status">

                        Status

                    </label>


                    <select
                        id="status"
                        name="status"
                        class="form-control"
                    >

                        <option
                            value="upcoming"
                            <?php
                            echo ($status === "upcoming")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Upcoming
                        </option>


                        <option
                            value="ongoing"
                            <?php
                            echo ($status === "ongoing")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Ongoing
                        </option>


                        <option
                            value="completed"
                            <?php
                            echo ($status === "completed")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Completed
                        </option>


                        <option
                            value="cancelled"
                            <?php
                            echo ($status === "cancelled")
                                ? "selected"
                                : "";
                            ?>
                        >
                            Cancelled
                        </option>

                    </select>

                </div>

            </div>



            <!-- =================================================
                 Event Image
            ================================================== -->

            <div class="form-group">

                <label for="image">

                    Event Image

                </label>


                <input
                    type="file"
                    id="image"
                    name="image"
                    class="form-control"
                    accept=".jpg,.jpeg,.png,.gif,.webp"
                >


                <div class="image-help">

                    Allowed formats:
                    JPG, JPEG, PNG, GIF, WEBP.
                    Maximum size: 5 MB.

                </div>

            </div>



            <!-- =================================================
                 Buttons
            ================================================== -->

            <div style="margin-top: 25px;">


                <button
                    type="submit"
                    class="btn btn-success"
                >

                    Add Event

                </button>


                <a
                    href="view_events.php"
                    class="btn btn-primary"
                >

                    View Events

                </a>


                <a
                    href="../dashboard.php"
                    class="btn"
                    style="background-color: #6b7280; color: white;"
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
<script src="../js/app.js"></script>

</body>

</html>