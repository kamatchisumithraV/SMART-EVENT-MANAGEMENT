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
| Build Export Query
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
| Prepare Query
|--------------------------------------------------------------------------
*/

$stmt =
    $conn->prepare($sql);


if ($stmt === false) {

    die("Database error: " . $conn->error);

}


/*
|--------------------------------------------------------------------------
| Bind Parameters
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
| Execute Query
|--------------------------------------------------------------------------
*/

$stmt->execute();


$result =
    $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Set CSV Headers
|--------------------------------------------------------------------------
*/

header(
    "Content-Type: text/csv; charset=utf-8"
);


header(
    "Content-Disposition: attachment; filename=event_report.csv"
);


/*
|--------------------------------------------------------------------------
| Open Output Stream
|--------------------------------------------------------------------------
*/

$output =
    fopen(
        "php://output",
        "w"
    );


/*
|--------------------------------------------------------------------------
| CSV Title
|--------------------------------------------------------------------------
*/

fputcsv(
    $output,
    [
        "S.No",
        "Event Name",
        "Category",
        "Event Date",
        "Start Time",
        "End Time",
        "Venue",
        "Maximum Participants",
        "Registrations",
        "Status"
    ]
);


/*
|--------------------------------------------------------------------------
| Export Data
|--------------------------------------------------------------------------
*/

$serial_no = 1;


while ($row = $result->fetch_assoc()) {


    fputcsv(
        $output,
        [
            $serial_no,

            $row["event_name"],

            $row["category"],

            date(
                "d-m-Y",
                strtotime(
                    $row["event_date"]
                )
            ),

            date(
                "h:i A",
                strtotime(
                    $row["start_time"]
                )
            ),

            !empty($row["end_time"])
                ? date(
                    "h:i A",
                    strtotime(
                        $row["end_time"]
                    )
                )
                : "",

            $row["venue"],

            $row["max_participants"],

            $row["total_registrations"],

            $row["status"]
        ]
    );


    $serial_no++;

}


/*
|--------------------------------------------------------------------------
| Close Resources
|--------------------------------------------------------------------------
*/

fclose($output);


$stmt->close();

$conn->close();


exit();

?>