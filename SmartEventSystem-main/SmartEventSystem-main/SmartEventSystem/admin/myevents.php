<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Fetch Registered Events
|--------------------------------------------------------------------------
*/

$sql = "SELECT
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
        ORDER BY e.event_date ASC";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database query failed: " . mysqli_error($conn));
}


mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

```
<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Events - Smart Event System</title>

<link rel="stylesheet" href="../css/style.css">
```

</head>

<body>

<header>

```
<h1>Smart Event System</h1>

<nav>

    <a href="../dashboard.php">Dashboard</a>

    <a href="register.php">Register Event</a>

    <a href="myevents.php">My Events</a>

    <a href="profile.php">Profile</a>

    <a href="../logout.php">Logout</a>

</nav>
```

</header>

<main>

```
<h2>My Registered Events</h2>


<?php

if (mysqli_num_rows($result) > 0) {

?>

    <table border="1" cellpadding="10" cellspacing="0">

        <thead>

            <tr>

                <th>S.No</th>

                <th>Event Name</th>

                <th>Category</th>

                <th>Date</th>

                <th>Start Time</th>

                <th>End Time</th>

                <th>Venue</th>

                <th>Status</th>

            </tr>

        </thead>


        <tbody>

            <?php

            $serial_no = 1;


            while ($row = mysqli_fetch_assoc($result)) {

            ?>

                <tr>

                    <td>
                        <?php echo $serial_no; ?>
                    </td>


                    <td>
                        <?php echo htmlspecialchars($row["event_name"]); ?>
                    </td>


                    <td>
                        <?php echo htmlspecialchars($row["category"]); ?>
                    </td>


                    <td>

                        <?php

                        echo date(
                            "d-m-Y",
                            strtotime($row["event_date"])
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        echo date(
                            "h:i A",
                            strtotime($row["start_time"])
                        );

                        ?>

                    </td>


                    <td>

                        <?php

                        if (!empty($row["end_time"])) {

                            echo date(
                                "h:i A",
                                strtotime($row["end_time"])
                            );

                        } else {

                            echo "-";

                        }

                        ?>

                    </td>


                    <td>
                        <?php echo htmlspecialchars($row["venue"]); ?>
                    </td>


                    <td>
                        <?php echo htmlspecialchars($row["status"]); ?>
                    </td>

                </tr>


            <?php

                $serial_no++;

            }

            ?>

        </tbody>

    </table>


<?php

} else {

?>

    <p>
        You have not registered for any events yet.
    </p>


    <a href="register.php">
        Register for an Event
    </a>


<?php

}

?>
```

</main>

<footer>

```
<p>
    &copy; <?php echo date("Y"); ?> Smart Event System
</p>
```

</footer>

</body>

</html>

<?php

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>
