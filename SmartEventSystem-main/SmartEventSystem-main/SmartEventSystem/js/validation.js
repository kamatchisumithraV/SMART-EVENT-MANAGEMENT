function validateRegistrationForm() {

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirm_password").value;


    // Check name

    if (name === "") {

        alert("Please enter your name.");

        return false;
    }


    // Check email

    if (email === "") {

        alert("Please enter your email.");

        return false;
    }


    // Basic email validation

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {

        alert("Please enter a valid email address.");

        return false;
    }


    // Check password length

    if (password.length < 6) {

        alert("Password must contain at least 6 characters.");

        return false;
    }


    // Check password confirmation

    if (password !== confirmPassword) {

        alert("Passwords do not match.");

        return false;
    }


    return true;
}