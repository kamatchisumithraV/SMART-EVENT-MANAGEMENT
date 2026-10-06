<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION["role"] !== "admin") {
    header("Location: ../dashboard.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Fetch Events
|--------------------------------------------------------------------------
*/

$sql = "SELECT
            id,
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
            status,
            created_at
        FROM events
        ORDER BY event_date ASC, start_time ASC";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Manage Events - Smart Event System</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

<style>

    /*
    |--------------------------------------------------------------------------
    | Table Container
    |--------------------------------------------------------------------------
    */

    .table-container {
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        margin-top: 25px;
        position: relative;
    }


    /*
    |--------------------------------------------------------------------------
    | Event Table
    |--------------------------------------------------------------------------
    */

    .event-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
        min-width: 1100px;
    }


    /*
    |--------------------------------------------------------------------------
    | Table Cells
    |--------------------------------------------------------------------------
    */

    .event-table th,
    .event-table td {
        padding: 14px;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        text-align: left;
        vertical-align: middle;
    }


    /*
    |--------------------------------------------------------------------------
    | Table Header
    |--------------------------------------------------------------------------
    */

    .event-table th {
        background-color: #1f2937;
        color: white;
    }


    /*
    |--------------------------------------------------------------------------
    | Alternating Rows
    |--------------------------------------------------------------------------
    */

    .event-table tbody tr:nth-child(even) {
        background-color: #f9fafb;
    }


    /*
    |--------------------------------------------------------------------------
    | Hover
    |--------------------------------------------------------------------------
    */

    .event-table tbody tr:hover {
        background-color: #f3f4f6;
    }


    /*
    |--------------------------------------------------------------------------
    | Event Name
    |--------------------------------------------------------------------------
    */

    .event-name {
        font-weight: 600;
        color: #1e3a8a;
        min-width: 150px;
    }


    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    */

    .event-description {
        max-width: 250px;
        min-width: 220px;
        line-height: 1.5;
    }


    /*
    |--------------------------------------------------------------------------
    | Event Image
    |--------------------------------------------------------------------------
    */

    .event-image {
        width: 100px;
        height: 70px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #ddd;
    }


    /*
    |--------------------------------------------------------------------------
    | No Image
    |--------------------------------------------------------------------------
    */

    .no-image {
        width: 100px;
        height: 70px;
        background-color: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        color: #6b7280;
        font-size: 13px;
    }


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    .status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
        white-space: nowrap;
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


    /*
    |--------------------------------------------------------------------------
    | ⭐ STICKY ACTION COLUMN
    |--------------------------------------------------------------------------
    |
    | This keeps Edit/Delete visible on the right side.
    |
    */

    .event-table th:last-child,
    .event-table td:last-child {

        position: sticky;

        right: 0;

        z-index: 10;

        background-color: white;

        min-width: 145px;

        width: 145px;

        box-shadow: -5px 0 10px rgba(0, 0, 0, 0.08);

    }


    /*
    |--------------------------------------------------------------------------
    | Sticky Header Action Cell
    |--------------------------------------------------------------------------
    */

    .event-table th:last-child {

        background-color: #1f2937;

        color: white;

        z-index: 20;

    }


    /*
    |--------------------------------------------------------------------------
    | Sticky Action Cell - Even Rows
    |--------------------------------------------------------------------------
    */

    .event-table tbody tr:nth-child(even) td:last-child {

        background-color: #f9fafb;

    }


    /*
    |--------------------------------------------------------------------------
    | Sticky Action Cell - Hover
    |--------------------------------------------------------------------------
    */

    .event-table tbody tr:hover td:last-child {

        background-color: #f3f4f6;

    }


    /*
    |--------------------------------------------------------------------------
    | Action Buttons
    |--------------------------------------------------------------------------
    */

    .action-buttons {

        display: flex;

        flex-direction: column;

        gap: 8px;

        align-items: stretch;

    }


    /*
    |--------------------------------------------------------------------------
    | Action Button Common Style
    |--------------------------------------------------------------------------
    */

    .action-buttons a {

        text-decoration: none;

        padding: 8px 12px;

        border-radius: 5px;

        color: white;

        font-size: 13px;

        text-align: center;

        font-weight: 600;

        display: block;

        white-space: nowrap;

    }


    /*
    |--------------------------------------------------------------------------
    | Edit Button
    |--------------------------------------------------------------------------
    */

    .btn-edit {

        background-color: #2563eb;

    }


    .btn-edit:hover {

        background-color: #1d4ed8;

    }


    /*
    |--------------------------------------------------------------------------
    | Delete Button
    |--------------------------------------------------------------------------
    */

    .btn-delete {

        background-color: #dc2626;

    }


    .btn-delete:hover {

        background-color: #b91c1c;

    }


    /*
    |--------------------------------------------------------------------------
    | Empty Message
    |--------------------------------------------------------------------------
    */

    .empty-message {

        background-color: #fef2f2;

        color: #7f1d1d;

        padding: 20px;

        border-radius: 6px;

        margin-top: 25px;

    }


    /*
    |--------------------------------------------------------------------------
    | Database Error
    |--------------------------------------------------------------------------
    */

    .database-error {

        background-color: #fee2e2;

        color: #991b1b;

        padding: 20px;

        border-radius: 6px;

        margin-top: 25px;

    }


    /*
    |--------------------------------------------------------------------------
    | Event Count
    |--------------------------------------------------------------------------
    */

    .event-count {

        margin-top: 20px;

        font-size: 16px;

        color: #4b5563;

    }


    /*
    |--------------------------------------------------------------------------
    | Mobile
    |--------------------------------------------------------------------------
    */

    @media (max-width: 768px) {

        .event-table th:last-child,
        .event-table td:last-child {

            min-width: 120px;

            width: 120px;

        }


        .action-buttons a {

            font-size: 12px;

            padding: 7px 8px;

        }

    }

</style>

</head>


<body>


<!-- Navigation -->

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



<!-- Main -->

<div class="container">

    <div class="page-title">

        <h1>
            Manage Events
        </h1>

        <p>
            View, edit and delete events.
        </p>

    </div>


    <!-- Buttons -->

    <div style="margin-top: 20px;">

        <a
            href="add_event.php"
            class="btn btn-success"
        >
            + Add New Event
        </a>

        <a
            href="../dashboard.php"
            class="btn btn-primary"
        >
            Dashboard
        </a>

    </div>


    <?php if ($result === false): ?>

        <div class="database-error">

            <strong>Database Error:</strong>

            <?php echo htmlspecialchars($conn->error); ?>

        </div>


    <?php elseif ($result->num_rows === 0): ?>

        <div class="empty-message">

            <strong>
                No events found.
            </strong>

            <p>
                There are currently no events in the database.
            </p>

            <a
                href="add_event.php"
                class="btn btn-success"
            >
                Add Your First Event
            </a>

        </div>


    <?php else: ?>

        <div class="event-count">

            Total Events:
            <strong>
                <?php echo $result->num_rows; ?>
            </strong>

        </div>


        <div class="table-container">

            <table class="event-table">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Image</th>

                        <th>Event Name</th>

                        <th>Description</th>

                        <th>Category</th>

                        <th>Date</th>

                        <th>Start Time</th>

                        <th>End Time</th>

                        <th>Venue</th>

                        <th>Max Participants</th>

                        <th>Registration Deadline</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php while ($event = $result->fetch_assoc()): ?>

                    <tr>

                        <!-- ID -->

                        <td>
                            <?php echo htmlspecialchars($event["id"]); ?>
                        </td>


                        <!-- Image -->

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

                            <div class="event-name">

                                <?php
                                echo htmlspecialchars(
                                    $event["event_name"]
                                );
                                ?>

                            </div>

                        </td>


                        <!-- Description -->

                        <td>

                            <div class="event-description">

                                <?php

                                $description =
                                    $event["description"];

                                if (strlen($description) > 150) {

                                    echo htmlspecialchars(
                                        substr($description, 0, 150)
                                    );

                                    echo "...";

                                } else {

                                    echo htmlspecialchars(
                                        $description
                                    );

                                }

                                ?>

                            </div>

                        </td>


                        <!-- Category -->

                        <td>

                            <?php

                            echo !empty($event["category"])
                                ? htmlspecialchars($event["category"])
                                : "—";

                            ?>

                        </td>


                        <!-- Date -->

                        <td>

                            <?php

                            echo date(
                                "d-m-Y",
                                strtotime($event["event_date"])
                            );

                            ?>

                        </td>


                        <!-- Start Time -->

                        <td>

                            <?php

                            echo !empty($event["start_time"])
                                ? date(
                                    "h:i A",
                                    strtotime($event["start_time"])
                                )
                                : "—";

                            ?>

                        </td>


                        <!-- End Time -->

                        <td>

                            <?php

                            echo !empty($event["end_time"])
                                ? date(
                                    "h:i A",
                                    strtotime($event["end_time"])
                                )
                                : "—";

                            ?>

                        </td>


                        <!-- Venue -->

                        <td>

                            <?php
                            echo htmlspecialchars($event["venue"]);
                            ?>

                        </td>


                        <!-- Maximum Participants -->

                        <td>

                            <?php
                            echo htmlspecialchars(
                                $event["max_participants"]
                            );
                            ?>

                        </td>


                        <!-- Registration Deadline -->

                        <td>

                            <?php

                            if (
                                !empty(
                                    $event["registration_deadline"]
                                )
                            ) {

                                echo date(
                                    "d-m-Y",
                                    strtotime(
                                        $event["registration_deadline"]
                                    )
                                );

                            } else {

                                echo "—";

                            }

                            ?>

                        </td>


                        <!-- Status -->

                        <td>

                            <?php

                            $status =
                                $event["status"] ?? "upcoming";

                            $status_class =
                                "status-" . strtolower($status);

                            ?>

                            <span
                                class="status <?php echo htmlspecialchars($status_class); ?>"
                            >

                                <?php
                                echo htmlspecialchars($status);
                                ?>

                            </span>

                        </td>


                        <!-- Actions -->

                        <td>

                            <div class="action-buttons">

                                <a
                                    href="edit_event.php?id=<?php echo $event["id"]; ?>"
                                    class="btn-edit"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete_event.php?id=<?php echo $event["id"]; ?>"
                                    class="btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this event?');"
                                >
                                    Delete
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>



<!-- Footer -->

<footer>

    <p>

        &copy; <?php echo date("Y"); ?>

        Smart Event System.

        All Rights Reserved.

    </p>

</footer>


</body>

</html>