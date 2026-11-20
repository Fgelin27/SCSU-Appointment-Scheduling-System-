<?php
session_start();

if (!isset($_SESSION['accountID'])) {
    header("Location: ../account_login.php");
    exit;
}

$providerId = (int) $_SESSION['accountID'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset = "UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Provider | Availability</title>
    <link rel="stylesheet" href="../css/scsu.css">
</head>

<body>
    <!--SCSU Naviagation -->
    <!-- <nav class="scsu-navbar"> -->
    <header class="scsu-header">
    <div class="scsu-header-content">

        <div class="scsu-brand">
            <img src="../image/SCSU.png" alt="SCSU logo" class="scsu-logo">

            <div class="scsu-brand-text">
                <h1>SCSU Appointment Scheduling System</h1>
            </div>
        </div>

    </div>
    </header>

    <main class="container provider-container">
    <h1>Manage Availability</h1>

    <p>
        Manage the hours when students can schedule appointments with you.
    </p>
    <div id="statusMessage" class="message" role="status" aria-live="polite"></div>

    <section class="provider-card">
        <h2 id="formTitle">Add Availability</h2>
        <form id="availabilityForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="availabilityDate">Date</label>

                    <input
                        type="date"
                        id="availabilityDate"
                        name="availability_date"
                        required
                    >
                </div>
                <div class="form-group">
                    <label for="startTime">StartTime</label>
                    <input
                        type="time"
                        id="startTime"
                        name="start_time"
                        required
                    >

                </div>
                <div class="form-group">
                    <label for="endTime">EndTime</label>
                        <input
                            type="time"
                            id="endTime"
                            name="end_time"
                            required
                        >
                </div>
                
            </div>
            <div class="form-grid">
                <button type="submit" class="primary-button" id="submitButton">
                    Add Availability
                </button>

                <button type="button" class="cancel-button" id="cancelButton" hidden> Cancel Edit
                </button>
            </div>
        </form>
    </section>
<!-- Previous availability section -->
    <section class="provider-card">
        <h2>Your Availability</h2>

        <div class="table-container">

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="availabilyTableBody">
                    <tr>
                        <td colspan="4">Loading availability.....</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    </main>


    <script>


        const API = {
            get: "../get_availability.php",
            save: "../save_availability.php",
            update: "../update_availability.php",
            delete: "../delete_availability.php"
        };

        const providerId = <?php echo json_encode($providerId); ?>;

        const availabilityForm = document.getElementById("availabilityForm");

        const dateInput = document.getElementById("availabilityDate");
        const startInput = document.getElementById("startTime");
        const endInput = document.getElementById("endTime");
        const tableBody = document.getElementById("availabilyTableBody");
        const statusMessage = document.getElementById("statusMessage");
        const formTitle = document.getElementById("formTitle");
        const submitButton = document.getElementById("submitButton");
        const cancelButton = document.getElementById("cancelButton");

    /*pageState*/
        let editingAvailabilityId = null;

    /*message*/
        function showMessage(message, type){
            statusMessage.textContent = message;
            statusMessage.className = "message " + type;
        }

        function clearMessage(){
            statusMessage.textContent = "";
            statusMessage.className = "message";
        }

    /*resetForm*/

        function resetForm(){
            availabilityForm.reset();
            editingAvailabilityId = null;
            formTitle.textContent = "Add availability";
            submitButton.textContent = "Add availability";
            cancelButton.hidden = true;
        }

    /*load availability*/

        async function loadAvailability() {
            try{
                const response = await fetch(
                    `${API.get}?provider_id=${providerId}`
                );
                const result = await response.json();

                if(!response.ok){
                    throw new Error(
                        result.message || "Could not retreive availability."
                    );
                }
                displayAvailability(result);
            } catch (error){
                console.error(error);

                tableBody.innerHTML = `
                <tr>
                    <td colspan="4">
                        Unable to load availability.
                    </td>
                </tr>
                `;
                showMessage(error.message, "error");
            }
        }

/*format the  time in AM PM format*/
        function formatTime(time) {
            const [hours, minutes] = time.split(":");

            const date = new Date();
            date.setHours(Number(hours), Number(minutes));

            return date.toLocaleTimeString("en-US", {
                hour: "2-digit",
                minute: "2-digit",
                hour12: true
            });
        }
          /* display availability */

        function displayAvailability(availabilityList){
            tableBody.innerHTML = "";
            if(!Array.isArray(availabilityList) || availabilityList.length === 0){
                tableBody.innerHTML = `
                    <tr>
                            <td colspan="4">
                                No availability has been added yet.
                            </td>
                        </tr>
                    `;
                    return;
            }

            availabilityList.forEach(function (availability){
                const row = document.createElement("tr");

                const dateCell = document.createElement("td");
                dateCell.textContent = availability.availability_date;

                const startCell = document.createElement("td");
                startCell.textContent = formatTime(availability.start_time);

                const endCell = document.createElement("td");
                endCell.textContent = formatTime(availability.end_time);

                const actionCell = document.createElement("td");
                actionCell.className = "actions";

                const editButton = document.createElement("button");
                editButton.textContent = "Edit";
                editButton.className = "edit-button";

                editButton.addEventListener("click", function () {
                    editAvailability(availability);
                });

                const deleteButton = document.createElement("button");
                deleteButton.textContent = "Delete";
                deleteButton.className = "delete-button";

                deleteButton.addEventListener("click", function () {
                    deleteAvailability(availability.availability_id);
                });

                actionCell.appendChild(editButton);
                actionCell.appendChild(deleteButton);

                row.appendChild(dateCell);
                row.appendChild(startCell);
                row.appendChild(endCell);
                row.appendChild(actionCell);
                tableBody.appendChild(row);

            });
        }

    /* Add or update availability*/

        availabilityForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            clearMessage();

            const availabilityDate = dateInput.value;
            const startTime = startInput.value;
            const endTime = endInput.value;

            if (endTime <= startTime){
                showMessage(
                    "The end time must be later than the start time.", "error"
                );
                return;
            }

            const formData = new FormData();

            formData.append("provider_id", providerId);
            formData.append("availability_date", availabilityDate);
            formData.append("start_time", startTime);
            formData.append("end_time", endTime);

            try{
                let url;
                if (editingAvailabilityId === null) {
                    url = API.save;
                } else {
                    url = API.update;
                    formData.append("availability_id", editingAvailabilityId);
                }

            const response = await fetch(url, {
                method: "POST",
                body: formData
            });
            const result = await response.json();

            if (!response.ok){
                throw new Error(
                    result.error || result.message || "Unable to save availability."
                );
            }

            if(editingAvailabilityId === null){
                showMessage(
                    "Availability added successfully.", "success"
                );
            } else{
                showMessage(
                    "Availability updated successfully.", "success"
                );
            }
            resetForm();
            await loadAvailability();
            } catch(error){
                console.error(error);
                showMessage(error.message, "error");
            }
        });

    /*edit availability*/

        function editAvailability(availability){
            clearMessage();
            editingAvailabilityId = availability.availability_id;
            dateInput.value = availability.availability_date;
            startInput.value = availability.start_time.substring(0, 5);
            endInput.value = availability.end_time.substring(0, 5);

            formTitle.textContent = "Edit Availability";
            submitButton.textContent = "Update Availability";
            cancelButton.hidden = false;

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        }

    /*delete availability*/

        
        async function deleteAvailability(availabilityId) {
            const confirmed = confirm(
                "Are you sure you want to delete this availability?"
            );

            if (!confirmed) return;

            clearMessage();

            try {
                const formData = new FormData();

                formData.append("availability_id", availabilityId);

                const response = await fetch(API.delete, {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.error || result.message || "Unable to delete availability."
                    );
                }

                showMessage("Availability deleted successfully.", "success");

                if (editingAvailabilityId === availabilityId) {
                    resetForm();
                }

                await loadAvailability();

            } catch (error) {
                console.error(error);
                showMessage(error.message, "error");
            }
        }


    /*cancel editing*/

        cancelButton.addEventListener("click", function (){
            resetForm();
            clearMessage();
        });

    /*page load*/
        loadAvailability();
    </script>




</body>



</html>
