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

$name = "";

$email = "";

$phone = "";

$role = "";

$created_at = "";


/*
|--------------------------------------------------------------------------
| Fetch User Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        name,
        email,
        phone,
        role,
        created_at
     FROM users
     WHERE id = ?"
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
| Check User Exists
|--------------------------------------------------------------------------
*/

if ($result->num_rows === 0) {

    $stmt->close();

    session_destroy();

    header("Location: ../login.php");
    exit();

}


$user = $result->fetch_assoc();


$stmt->close();


/*
|--------------------------------------------------------------------------
| Load Existing Values
|--------------------------------------------------------------------------
*/

$name = $user["name"];

$email = $user["email"];

$phone = $user["phone"];

$role = $user["role"];

$created_at = $user["created_at"];


/*
|--------------------------------------------------------------------------
| Handle Profile Update
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | Get Form Values
    |--------------------------------------------------------------------------
    */

    $name =
        trim($_POST["name"] ?? "");


    $email =
        trim($_POST["email"] ?? "");


    $phone =
        trim($_POST["phone"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        empty($name) ||
        empty($email)
    ) {

        $message =
            "Name and email are required.";

        $message_type =
            "danger";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $message_type =
            "danger";

    } elseif (
        !empty($phone) &&
        !preg_match(
            "/^[0-9]{10,15}$/",
            $phone
        )
    ) {

        $message =
            "Phone number must contain 10 to 15 digits.";

        $message_type =
            "danger";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Check Email Availability
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
             AND id != ?"
        );


        if ($stmt === false) {

            $message =
                "Database error: "
                . $conn->error;

            $message_type =
                "danger";

        } else {


            $stmt->bind_param(
                "si",
                $email,
                $user_id
            );


            $stmt->execute();


            $email_result =
                $stmt->get_result();


            /*
            |--------------------------------------------------------------------------
            | Email Already Exists
            |--------------------------------------------------------------------------
            */

            if ($email_result->num_rows > 0) {

                $message =
                    "This email address is already registered.";

                $message_type =
                    "danger";

            }


            $stmt->close();

        }


        /*
        |--------------------------------------------------------------------------
        | Update User Information
        |--------------------------------------------------------------------------
        */

        if (empty($message)) {


            $stmt = $conn->prepare(
                "UPDATE users
                 SET
                    name = ?,
                    email = ?,
                    phone = ?
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
                    "sssi",
                    $name,
                    $email,
                    $phone,
                    $user_id
                );


                /*
                |--------------------------------------------------------------------------
                | Execute Update
                |--------------------------------------------------------------------------
                */

                if ($stmt->execute()) {

                    $message =
                        "Profile updated successfully!";

                    $message_type =
                        "success";


                    /*
                    |--------------------------------------------------------------------------
                    | Update Session Email
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION["email"] =
                        $email;

                } else {

                    $message =
                        "Failed to update profile: "
                        . $stmt->error;

                    $message_type =
                        "danger";

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
        My Profile - Smart Event System
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .profile-card {

            max-width: 700px;

            margin: 0 auto;

        }


        .profile-header {

            text-align: center;

            margin-bottom: 30px;

        }


        .profile-icon {

            width: 90px;

            height: 90px;

            margin: 0 auto 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 50%;

            background-color: #1f2937;

            color: white;

            font-size: 36px;

            font-weight: bold;

        }


        .profile-header h2 {

            margin-bottom: 5px;

        }


        .profile-header p {

            color: #6b7280;

            margin: 0;

        }


        .profile-info {

            margin-bottom: 30px;

        }


        .info-row {

            display: grid;

            grid-template-columns: 180px 1fr;

            gap: 20px;

            padding: 14px 0;

            border-bottom: 1px solid #e5e7eb;

        }


        .info-label {

            font-weight: bold;

            color: #374151;

        }


        .info-value {

            color: #4b5563;

        }


        .role-badge {

            display: inline-block;

            padding: 5px 12px;

            border-radius: 20px;

            background-color: #dbeafe;

            color: #1d4ed8;

            font-size: 13px;

            font-weight: bold;

            text-transform: capitalize;

        }


        .form-section {

            margin-top: 30px;

            padding-top: 25px;

            border-top: 1px solid #e5e7eb;

        }


        .form-section h3 {

            margin-bottom: 20px;

            color: #1f2937;

        }


        .required {

            color: #dc2626;

        }


        .profile-buttons {

            margin-top: 25px;

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }


        @media (max-width: 768px) {

            .info-row {

                grid-template-columns: 1fr;

                gap: 5px;

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
            My Profile
        </h1>

        <p>
            View and update your profile information.
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
         Profile Card
    ====================================================== -->

    <div class="card profile-card">


        <!-- Profile Header -->

        <div class="profile-header">


            <div class="profile-icon">

                <?php

                echo strtoupper(
                    substr(
                        $name,
                        0,
                        1
                    )
                );

                ?>

            </div>


            <h2>

                <?php

                echo htmlspecialchars(
                    $name
                );

                ?>

            </h2>


            <p>

                <?php

                echo htmlspecialchars(
                    $email
                );

                ?>

            </p>


        </div>



        <!-- =================================================
             Account Information
        ================================================== -->

        <div class="profile-info">


            <h3>
                Account Information
            </h3>


            <!-- Name -->

            <div class="info-row">


                <div class="info-label">

                    Name

                </div>


                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $name
                    );

                    ?>

                </div>


            </div>



            <!-- Email -->

            <div class="info-row">


                <div class="info-label">

                    Email

                </div>


                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $email
                    );

                    ?>

                </div>


            </div>



            <!-- Phone -->

            <div class="info-row">


                <div class="info-label">

                    Phone

                </div>


                <div class="info-value">

                    <?php

                    if (!empty($phone)) {

                        echo htmlspecialchars(
                            $phone
                        );

                    } else {

                        echo "Not provided";

                    }

                    ?>

                </div>


            </div>



            <!-- Role -->

            <div class="info-row">


                <div class="info-label">

                    Role

                </div>


                <div class="info-value">


                    <span class="role-badge">

                        <?php

                        echo htmlspecialchars(
                            $role
                        );

                        ?>

                    </span>


                </div>


            </div>



            <!-- Account Created -->

            <div class="info-row">


                <div class="info-label">

                    Account Created

                </div>


                <div class="info-value">

                    <?php

                    echo date(
                        "d-m-Y h:i A",
                        strtotime($created_at)
                    );

                    ?>

                </div>


            </div>


        </div>



        <!-- =================================================
             Edit Profile
        ================================================== -->

        <div class="form-section">


            <h3>
                Update Profile
            </h3>


            <form
                method="POST"
                action="profile.php"
            >


                <!-- Name -->

                <div class="form-group">


                    <label for="name">

                        Name

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-control"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >


                </div>



                <!-- Email -->

                <div class="form-group">


                    <label for="email">

                        Email

                        <span class="required">
                            *
                        </span>

                    </label>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >


                </div>



                <!-- Phone -->

                <div class="form-group">


                    <label for="phone">

                        Phone

                    </label>


                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control"
                        value="<?php echo htmlspecialchars($phone ?? ""); ?>"
                        maxlength="15"
                        pattern="[0-9]{10,15}"
                    >


                </div>



                <!-- Buttons -->

                <div class="profile-buttons">


                    <button
                        type="submit"
                        class="btn btn-success"
                    >

                        Update Profile

                    </button>


                    <a
                        href="../dashboard.php"
                        class="btn btn-primary"
                    >

                        Back to Dashboard

                    </a>


                </div>


            </form>


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

$conn->close();

?>


</body>

</html>