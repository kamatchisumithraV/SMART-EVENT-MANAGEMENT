/*
|--------------------------------------------------------------------------
| SmartEventSystem - Application JavaScript
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Wait for Page to Load
|--------------------------------------------------------------------------
*/

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | Initialize Application
    |--------------------------------------------------------------------------
    */

    initializeNavigation();
    initializeForms();
    initializeSearch();
    initializeAlerts();
    initializeDeleteConfirmation();
    initializePasswordToggle();
});


/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

function initializeNavigation() {

    const menuButton = document.querySelector(".menu-button");
    const navigation = document.querySelector(".navigation");

    if (menuButton && navigation) {

        menuButton.addEventListener("click", function () {

            navigation.classList.toggle("active");

        });

    }
}


/*
|--------------------------------------------------------------------------
| Form Validation
|--------------------------------------------------------------------------
*/

function initializeForms() {

    const forms = document.querySelectorAll("form");

    forms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            const requiredFields =
                form.querySelectorAll("[required]");

            let isValid = true;


            /*
            |--------------------------------------------------------------------------
            | Check Required Fields
            |--------------------------------------------------------------------------
            */

            requiredFields.forEach(function (field) {

                if (field.value.trim() === "") {

                    isValid = false;

                    field.classList.add("input-error");

                } else {

                    field.classList.remove("input-error");

                }

            });


            /*
            |--------------------------------------------------------------------------
            | Stop Form Submission if Invalid
            |--------------------------------------------------------------------------
            */

            if (!isValid) {

                event.preventDefault();

                alert(
                    "Please fill in all required fields."
                );

            }

        });

    });
}


/*
|--------------------------------------------------------------------------
| Search Functionality
|--------------------------------------------------------------------------
*/

function initializeSearch() {

    const searchInput =
        document.getElementById("searchInput");

    const table =
        document.querySelector("table");

    if (!searchInput || !table) {
        return;
    }


    searchInput.addEventListener("keyup", function () {

        const searchValue =
            searchInput.value.toLowerCase();

        const rows =
            table.querySelectorAll("tbody tr");


        rows.forEach(function (row) {

            const rowText =
                row.textContent.toLowerCase();

            if (rowText.includes(searchValue)) {

                row.style.display = "";

            } else {

                row.style.display = "none";

            }

        });

    });
}


/*
|--------------------------------------------------------------------------
| Alert Messages
|--------------------------------------------------------------------------
*/

function initializeAlerts() {

    const alerts =
        document.querySelectorAll(".alert");

    alerts.forEach(function (alert) {

        setTimeout(function () {

            alert.style.opacity = "0";

            setTimeout(function () {

                alert.remove();

            }, 500);

        }, 4000);

    });
}


/*
|--------------------------------------------------------------------------
| Delete Confirmation
|--------------------------------------------------------------------------
*/

function initializeDeleteConfirmation() {

    const deleteButtons =
        document.querySelectorAll(".delete-button");


    deleteButtons.forEach(function (button) {

        button.addEventListener("click", function (event) {

            const confirmation =
                confirm(
                    "Are you sure you want to delete this event?"
                );


            if (!confirmation) {

                event.preventDefault();

            }

        });

    });
}


/*
|--------------------------------------------------------------------------
| Password Visibility Toggle
|--------------------------------------------------------------------------
*/

function initializePasswordToggle() {

    const toggleButtons =
        document.querySelectorAll(".password-toggle");


    toggleButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const targetId =
                button.getAttribute("data-target");

            const passwordField =
                document.getElementById(targetId);


            if (!passwordField) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Change Password Field Type
            |--------------------------------------------------------------------------
            */

            if (passwordField.type === "password") {

                passwordField.type = "text";

                button.textContent = "Hide";

            } else {

                passwordField.type = "password";

                button.textContent = "Show";

            }

        });

    });
}


/*
|--------------------------------------------------------------------------
| Event Date Validation
|--------------------------------------------------------------------------
*/

function validateEventDate() {

    const eventDate =
        document.getElementById("event_date");

    if (!eventDate) {
        return true;
    }


    const selectedDate =
        new Date(eventDate.value);

    const today =
        new Date();


    today.setHours(0, 0, 0, 0);


    if (selectedDate < today) {

        alert(
            "Event date cannot be in the past."
        );

        eventDate.focus();

        return false;

    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Event Time Validation
|--------------------------------------------------------------------------
*/

function validateEventTime() {

    const startTime =
        document.getElementById("start_time");

    const endTime =
        document.getElementById("end_time");


    if (!startTime || !endTime) {
        return true;
    }


    if (
        startTime.value !== "" &&
        endTime.value !== "" &&
        endTime.value <= startTime.value
    ) {

        alert(
            "End time must be later than start time."
        );

        endTime.focus();

        return false;

    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Registration Capacity Validation
|--------------------------------------------------------------------------
*/

function validateCapacity() {

    const capacity =
        document.getElementById("max_participants");


    if (!capacity) {
        return true;
    }


    const value =
        parseInt(capacity.value);


    if (isNaN(value) || value <= 0) {

        alert(
            "Maximum participants must be greater than 0."
        );

        capacity.focus();

        return false;

    }

    return true;
}


/*
|--------------------------------------------------------------------------
| Event Form Validation
|--------------------------------------------------------------------------
*/

function validateEventForm() {

    if (!validateEventDate()) {
        return false;
    }


    if (!validateEventTime()) {
        return false;
    }


    if (!validateCapacity()) {
        return false;
    }


    return true;
}


/*
|--------------------------------------------------------------------------
| Character Counter
|--------------------------------------------------------------------------
*/

function initializeCharacterCounter() {

    const textareas =
        document.querySelectorAll(
            "textarea[data-maxlength]"
        );


    textareas.forEach(function (textarea) {

        const maxLength =
            parseInt(
                textarea.getAttribute("data-maxlength")
            );


        const counter =
            document.createElement("small");

        counter.className =
            "character-counter";


        textarea.parentNode.appendChild(counter);


        function updateCounter() {

            const currentLength =
                textarea.value.length;

            counter.textContent =
                currentLength +
                " / " +
                maxLength +
                " characters";

        }


        textarea.addEventListener(
            "input",
            updateCounter
        );


        updateCounter();

    });
}


/*
|--------------------------------------------------------------------------
| Initialize Character Counter
|--------------------------------------------------------------------------
*/

document.addEventListener(
    "DOMContentLoaded",
    initializeCharacterCounter
);


/*
|--------------------------------------------------------------------------
| Print Report
|--------------------------------------------------------------------------
*/

function printReport() {

    window.print();

}


/*
|--------------------------------------------------------------------------
| Confirm Registration
|--------------------------------------------------------------------------
*/

function confirmRegistration() {

    return confirm(
        "Do you want to register for this event?"
    );

}


/*
|--------------------------------------------------------------------------
| Confirm Logout
|--------------------------------------------------------------------------
*/

function confirmLogout() {

    return confirm(
        "Are you sure you want to logout?"
    );

}