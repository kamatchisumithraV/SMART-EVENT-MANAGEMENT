<?php

session_start();

require_once "../config/db.php";

$message = "";
$message_type = "";

$name = "";
$email = "";
$phone = "";

/*
|--------------------------------------------------------------------------
| Handle Account Registration
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if (
        $name === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "danger";

    } elseif ($phone !== "" && !preg_match("/^[0-9]{10,15}$/", $phone)) {

        $message = "Please enter a valid phone number.";
        $message_type = "danger";

    } elseif (strlen($password) < 6) {

        $message = "Password must contain at least 6 characters.";
        $message_type = "danger";

    } elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "danger";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check Existing Email
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        if (!$stmt) {

            $message = "Database error: " . $conn->error;
            $message_type = "danger";

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $message = "An account with this email already exists.";
                $message_type = "danger";

                $stmt->close();

            } else {

                $stmt->close();

                /*
                |--------------------------------------------------------------------------
                | Hash Password
                |--------------------------------------------------------------------------
                */

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                /*
                |--------------------------------------------------------------------------
                | Insert User
                |--------------------------------------------------------------------------
                |
                | phone can be NULL.
                | role is set to 'user'.
                | created_at is handled automatically by MySQL.
                |
                */

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (name, email, phone, password, role)
                    VALUES (?, ?, ?, ?, 'user')"
                );

                if (!$stmt) {

                    $message = "Database error: " . $conn->error;
                    $message_type = "danger";

                } else {

                    /*
                    |--------------------------------------------------------------------------
                    | If phone is empty, store NULL
                    |--------------------------------------------------------------------------
                    */

                    $phone_value = ($phone === "") ? null : $phone;

                    $stmt->bind_param(
                        "ssss",
                        $name,
                        $email,
                        $phone_value,
                        $hashed_password
                    );

                    if ($stmt->execute()) {
                  
 $stmt->close();

    header("Location: ../login.php?registered=success");
    exit();

                    } else {

                        $message =
                            "Registration failed: " . $stmt->error;

                        $message_type = "danger";
                    }

                    $stmt->close();
                }
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
        Register - Smart Event System
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <link
        rel="stylesheet"
        href="../css/login.css"
    >

</head>

<body>

<div class="login-page">

    <div class="login-container">

        <h2>
            Smart Event System
        </h2>

        <h3
            style="
                text-align: center;
                margin-bottom: 20px;
            "
        >
            Create Account
        </h3>

        <?php if ($message !== ""): ?>

            <div class="alert alert-<?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action="user_register.php"
            onsubmit="return validateRegistrationForm();"
        >

            <!-- Name -->

            <div class="form-group">

                <label for="name">
                    Full Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($name); ?>"
                    required
                >

            </div>


            <!-- Email -->

            <div class="form-group">

                <label for="email">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    required
                >

            </div>


            <!-- Phone -->

            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    class="form-control"
                    placeholder="Enter your phone number"
                    maxlength="15"
                    value="<?php echo htmlspecialchars($phone); ?>"
                >

            </div>


            <!-- Password -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control"
                    placeholder="Enter password"
                    minlength="6"
                    required
                >

            </div>


            <!-- Confirm Password -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    class="form-control"
                    placeholder="Confirm password"
                    minlength="6"
                    required
                >

            </div>


            <!-- Submit -->

            <button
                type="submit"
                class="btn btn-primary"
            >
                Register
            </button>

        </form>


        <div class="login-footer">

            <p>

                Already have an account?

                <a href="../login.php">
                    Login
                </a>

            </p>

            <p style="margin-top: 10px;">

                <a href="../index.php">
                    ← Back to Home
                </a>

            </p>

        </div>

    </div>

</div>


<script src="../js/validation.js"></script>

</body>

</html>