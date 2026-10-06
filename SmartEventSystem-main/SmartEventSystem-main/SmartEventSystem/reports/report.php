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

$event_id = "";

$start_date = "";

$end_date = "";

$status = "";

$message = "";

$message_type = "";

$total_events = 0;

$total_registrations = 0;


/*
|--------------------------------------------------------------------------
| Get Filter Values
|--------------------------------------------------------------------------
*/

$event_id =
    $_GET["event_id"] ?? "";

$start_date =
    $_GET["start_date"] ?? "";

$end_date =
    $_GET["end_date"] ?? "";

$status =
    $_GET["status"] ?? "";


/*
|--------------------------------------------------------------------------
| Fetch Event List
|--------------------------------------------------------------------------
*/

$event_stmt = $conn->prepare(
    "SELECT
        id,
        event_name
     FROM events
     ORDER BY event_date ASC"
);


if ($event_stmt === false) {

    die("Database error: " . $conn->error);

}


$event_stmt->execute();


$event_result =
    $event_stmt->get_result();


/*
|--------------------------------------------------------------------------
| Build Report Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        e.id,
        e.event_name,
        e.category,
        e.event_date,
        e.start_time,
        e.end_time,
        e.venue,
        e.max_participants,
        e.status,
        COUNT(r.id) AS total_registrations
    FROM events e
    LEFT JOIN registrations r
        ON e.id = r.event_id
";


$conditions = [];

$parameters = [];

$parameter_types = "";


/*
|--------------------------------------------------------------------------
| Event Filter
|--------------------------------------------------------------------------
*/

if (
    !empty($event_id) &&
    is_numeric($event_id)
) {

    $conditions[] =
        "e.id = ?";

    $parameters[] =
        (int)$event_id;

    $parameter_types .= "i";

}


/*
|--------------------------------------------------------------------------
| Start Date Filter
|--------------------------------------------------------------------------
*/

if (!empty($start_date)) {

    $conditions[] =
        "e.event_date >= ?";

    $parameters[] =
        $start_date;

    $parameter_types .= "s";

}


/*
|--------------------------------------------------------------------------
| End Date Filter
|--------------------------------------------------------------------------
*/

if (!empty($end_date)) {

    $conditions[] =
        "e.event_date <= ?";

    $parameters[] =
        $end_date;

    $parameter_types .= "s";

}


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

if (!empty($status)) {

    $conditions[] =
        "e.status = ?";

    $parameters[] =
        $status;

    $parameter_types .= "s";

}


/*
|--------------------------------------------------------------------------
| Add Conditions
|--------------------------------------------------------------------------
*/

if (!empty($conditions)) {

    $sql .=
        " WHERE "
        . implode(
            " AND ",
            $conditions
        );

}


/*
|--------------------------------------------------------------------------
| Group and Order
|--------------------------------------------------------------------------
*/

$sql .= "
    GROUP BY
        e.id,
        e.event_name,
        e.category,
        e.event_date,
        e.start_time,
        e.end_time,
        e.venue,
        e.max_participants,
        e.status
    ORDER BY
        e.event_date ASC,
        e.start_time ASC
";


/*
|--------------------------------------------------------------------------
| Prepare Report Query
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare($sql);


if ($stmt === false) {

    die("Database error: " . $conn->error);

}


/*
|--------------------------------------------------------------------------
| Bind Filter Parameters
|--------------------------------------------------------------------------
*/

if (!empty($parameters)) {

    $bind_values = [];

    $bind_values[] =
        $parameter_types;


    foreach ($parameters as $key => $value) {

        $bind_values[] =
            &$parameters[$key];

    }


    call_user_func_array(
        [$stmt, "bind_param"],
        $bind_values
    );

}


/*
|--------------------------------------------------------------------------
| Execute Report Query
|--------------------------------------------------------------------------
*/

$stmt->execute();


$result =
    $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Calculate Summary
|--------------------------------------------------------------------------
*/

$total_events =
    $result->num_rows;


while ($summary_row = $result->fetch_assoc()) {

    $total_registrations +=
        (int)$summary_row["total_registrations"];

}


$result->data_seek(0);

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
        Reports - Smart Event System
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .report-filters {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .summary-container {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

            margin-bottom: 30px;

        }


        .summary-card {

            padding: 25px;

            text-align: center;

            border-radius: 8px;

            background-color: #f3f4f6;

        }


        .summary-card h3 {

            margin-bottom: 10px;

            color: #374151;

        }


        .summary-number {

            font-size: 30px;

            font-weight: bold;

            color: #1f2937;

        }


        .report-table-container {

            width: 100%;

            overflow-x: auto;

        }


        .report-table {

            width: 100%;

            border-collapse: collapse;

        }


        .report-table th {

            background-color: #1f2937;

            color: white;

            padding: 14px;

            text-align: left;

        }


        .report-table td {

            padding: 12px;

            border-bottom: 1px solid #ddd;

        }


        .report-table tr:hover {

            background-color: #f9fafb;

        }


        .report-actions {

            margin-bottom: 25px;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        .status-badge {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 15px;

            font-size: 12px;

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


        @media (max-width: 900px) {

            .report-filters {

                grid-template-columns:
                    1fr 1fr;

            }

        }


        @media (max-width: 600px) {

            .report-filters {

                grid-template-columns:
                    1fr;

            }


            .summary-container {

                grid-template-columns:
                    1fr;

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


            <a href="../admin/view_events.php">
                Events
            </a>


            <a href="report.php">
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
            Event Reports
        </h1>

        <p>
            View event and registration reports.
        </p>

    </div>



    <!-- =====================================================
         Filters
    ====================================================== -->

    <div class="card">


        <form
            method="GET"
            action="report.php"
        >


            <div class="report-filters">


                <!-- Event -->

                <div class="form-group">

                    <label for="event_id">

                        Event

                    </label>


                    <select
                        id="event_id"
                        name="event_id"
                        class="form-control"
                    >

                        <option value="">

                            All Events

                        </option>


                        <?php while ($event = $event_result->fetch_assoc()): ?>


                            <option
                                value="<?php echo $event["id"]; ?>"
                                <?php
                                echo (string)$event_id ===
                                     (string)$event["id"]
                                    ? "selected"
                                    : "";
                                ?>
                            >

                                <?php

                                echo htmlspecialchars(
                                    $event["event_name"]
                                );

                                ?>

                            </option>


                        <?php endwhile; ?>

                    </select>

                </div>



                <!-- Start Date -->

                <div class="form-group">

                    <label for="start_date">

                        From Date

                    </label>


                    <input
                        type="date"
                        id="start_date"
                        name="start_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($start_date); ?>"
                    >

                </div>



                <!-- End Date -->

                <div class="form-group">

                    <label for="end_date">

                        To Date

                    </label>


                    <input
                        type="date"
                        id="end_date"
                        name="end_date"
                        class="form-control"
                        value="<?php echo htmlspecialchars($end_date); ?>"
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

                        <option value="">

                            All Status

                        </option>


                        <option
                            value="upcoming"
                            <?php
                            echo $status === "upcoming"
                                ? "selected"
                                : "";
                            ?>
                        >

                            Upcoming

                        </option>


                        <option
                            value="ongoing"
                            <?php
                            echo $status === "ongoing"
                                ? "selected"
                                : "";
                            ?>
                        >

                            Ongoing

                        </option>


                        <option
                            value="completed"
                            <?php
                            echo $status === "completed"
                                ? "selected"
                                : "";
                            ?>
                        >

                            Completed

                        </option>


                        <option
                            value="cancelled"
                            <?php
                            echo $status === "cancelled"
                                ? "selected"
                                : "";
                            ?>
                        >

                            Cancelled

                        </option>

                    </select>

                </div>


            </div>



            <!-- Filter Buttons -->

            <button
                type="submit"
                class="btn btn-primary"
            >

                Generate Report

            </button>


            <a
                href="report.php"
                class="btn"
            >

                Clear Filters

            </a>


        </form>


    </div>



    <!-- =====================================================
         Summary
    ====================================================== -->

    <div class="summary-container">


        <div class="summary-card">


            <h3>
                Total Events
            </h3>


            <div class="summary-number">

                <?php

                echo $total_events;

                ?>

            </div>


        </div>



        <div class="summary-card">


            <h3>
                Total Registrations
            </h3>


            <div class="summary-number">

                <?php

                echo $total_registrations;

                ?>

            </div>


        </div>


    </div>



    <!-- =====================================================
         Export
    ====================================================== -->

    <div class="report-actions">


        <a
            href="export.php?event_id=<?php echo urlencode($event_id); ?>&start_date=<?php echo urlencode($start_date); ?>&end_date=<?php echo urlencode($end_date); ?>&status=<?php echo urlencode($status); ?>"
            class="btn btn-success"
        >

            Export Report

        </a>


    </div>



    <!-- =====================================================
         Report Table
    ====================================================== -->

    <div class="card">


        <div class="report-table-container">


            <table class="report-table">


                <thead>

                    <tr>

                        <th>
                            S.No
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
                            Venue
                        </th>

                        <th>
                            Maximum Participants
                        </th>

                        <th>
                            Registrations
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>


                    <?php if ($result->num_rows > 0): ?>


                        <?php

                        $serial_no = 1;

                        ?>


                        <?php while ($row = $result->fetch_assoc()): ?>


                            <tr>


                                <td>

                                    <?php

                                    echo $serial_no;

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $row["event_name"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $row["category"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo date(
                                        "d-m-Y",
                                        strtotime(
                                            $row["event_date"]
                                        )
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $row["venue"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo (int)
                                        $row["max_participants"];

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo (int)
                                        $row["total_registrations"];

                                    ?>

                                </td>


                                <td>


                                    <span
                                        class="status-badge status-<?php echo htmlspecialchars($row["status"]); ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $row["status"]
                                        );

                                        ?>

                                    </span>


                                </td>


                            </tr>


                            <?php

                            $serial_no++;

                            ?>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                style="text-align: center;"
                            >

                                No report data found.

                            </td>

                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>


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

$event_stmt->close();

$conn->close();

?>


</body>

</html>