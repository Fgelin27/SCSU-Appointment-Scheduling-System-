
<?php
session_start();

if (
    empty($_SESSION['accountID']) ||
    strtolower(trim($_SESSION['role'] ?? '')) !== 'student'
) {
    header('Location: ../account_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment | SCSU</title>

    <link rel="stylesheet" href="../css/scsu.css">
</head>

<body>

<header class="scsu-header">
    <div class="scsu-header-content">
        <div class="scsu-brand">
            <img
                src="../image/SCSU.png"
                alt="SCSU Logo"
                class="scsu-logo"
            >

            <div class="scsu-brand-text">
                <h1>SCSU Appointment Scheduling System</h1>
            </div>
        </div>
    </div>
</header>

<main class="container provider-container">

    <section class="card provider-card">

        <h1>Book an Appointment</h1>

        <p>
            Select a provider and appointment date to view
            available appointment times.
        </p>

        <form id="bookingForm">

            <div class="form-grid booking-form-grid">

                <div class="form-group">
                    <label for="provider">Select Provider</label>

                    <select id="provider" name="provider_id" required>
                        <option value="">Loading providers...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointmentDate">Appointment Date</label>

                    <input
                        type="date"
                        id="appointmentDate"
                        name="appointment_date"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="appointmentSlot">Available Time</label>

                    <select
                        id="appointmentSlot"
                        name="appointment_slot"
                        required
                        disabled
                    >
                        <option value="">Select a provider and date first</option>
                    </select>
                </div>

            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    id="bookButton"
                    class="primary-button"
                    disabled
                >
                    Book Appointment
                </button>
            </div>

        </form>

        <div
            id="bookingStatus"
            class="status-message"
            role="status"
            aria-live="polite"
        ></div>

    </section>

</main>

<script>
const API = {
    providers: "get_providers.php",
    slots: "get_bookable_slots.php",
    create: "create_appointment.php"
};

const providerSelect = document.getElementById("provider");
const dateInput = document.getElementById("appointmentDate");
const slotSelect = document.getElementById("appointmentSlot");
const bookingForm = document.getElementById("bookingForm");
const bookButton = document.getElementById("bookButton");
const statusMessage = document.getElementById("bookingStatus");

let availableSlots = [];
let slotRequestNumber = 0;

// Use the browser's local date instead of UTC.
function getLocalDate() {
    const now = new Date();

    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, "0");
    const day = String(now.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
}

dateInput.min = getLocalDate();

function showStatus(message, type = "") {
    statusMessage.textContent = message;
    statusMessage.className = "status-message";

    if (type) {
        statusMessage.classList.add(type);
    }
}

function resetSlots(message = "Select a provider and date first") {
    availableSlots = [];

    slotSelect.innerHTML = "";

    const option = document.createElement("option");
    option.value = "";
    option.textContent = message;

    slotSelect.appendChild(option);
    slotSelect.disabled = true;

    bookButton.disabled = true;
}

async function fetchJson(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        cache: "no-store"
    });

    let data;

    try {
        data = await response.json();
    } catch {
        throw new Error("The server returned an invalid response.");
    }

    if (!response.ok) {
        throw new Error(data.error || "The request failed.");
    }

    return data;
}

// Retrieve all providers available to students.
async function loadProviders() {
    providerSelect.disabled = true;
    providerSelect.innerHTML = '<option value="">Loading providers...</option>';

    try {
        const data = await fetchJson(API.providers);

        providerSelect.innerHTML = "";

        if (!data.providers || data.providers.length === 0) {
            providerSelect.innerHTML =
                '<option value="">No providers available</option>';

            showStatus("There are currently no providers available.", "error");
            return;
        }

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "Choose a provider";

        providerSelect.appendChild(placeholder);

        data.providers.forEach(provider => {
            const option = document.createElement("option");

            option.value = provider.AccountID;

            const fullName =
                `${provider.First_name} ${provider.Last_name}`;

            option.textContent =
                `${fullName} (${provider.Role})`;

            providerSelect.appendChild(option);
        });

        providerSelect.disabled = false;

    } catch (error) {
        providerSelect.innerHTML =
            '<option value="">Could not load providers</option>';

        showStatus(error.message, "error");
    }
}

// Retrieve available 30-minute slots for the selected provider and date.
async function loadSlots() {
    const providerId = providerSelect.value;
    const date = dateInput.value;

    resetSlots("Loading available times...");
    showStatus();

    if (!providerId || !date) {
        resetSlots();
        return;
    }

    if (date < getLocalDate()) {
        resetSlots();
        showStatus("Please choose today or a future date.", "error");
        return;
    }

    const requestNumber = ++slotRequestNumber;

    try {
        const params = new URLSearchParams({
            provider_id: providerId,
            date: date
        });

        const data = await fetchJson(
            `${API.slots}?${params.toString()}`
        );

        // Ignore an older response if the user changed their selection.
        if (requestNumber !== slotRequestNumber) {
            return;
        }

        availableSlots = data.slots || [];

        slotSelect.innerHTML = "";

        if (availableSlots.length === 0) {
            resetSlots("No available appointment times");

            showStatus(
                "No appointment times are available for this provider on that date.",
                "error"
            );

            return;
        }

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "Choose an appointment time";

        slotSelect.appendChild(placeholder);

        availableSlots.forEach((slot, index) => {
            const option = document.createElement("option");

            option.value = index;

            option.textContent =
                `${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}`;

            slotSelect.appendChild(option);
        });

        slotSelect.disabled = false;

        showStatus(
            `${availableSlots.length} appointment time(s) available.`,
            "success"
        );

    } catch (error) {
        if (requestNumber !== slotRequestNumber) {
            return;
        }

        resetSlots("Could not load appointment times");
        showStatus(error.message, "error");
    }
}

// Convert 24-hour time into a readable 12-hour format.
function formatTime(time) {
    const [hourText, minute] = time.split(":");
    const hour = Number(hourText);

    const suffix = hour >= 12 ? "PM" : "AM";
    const displayHour = hour % 12 || 12;

    return `${displayHour}:${minute} ${suffix}`;
}

providerSelect.addEventListener("change", loadSlots);
dateInput.addEventListener("change", loadSlots);

slotSelect.addEventListener("change", () => {
    bookButton.disabled = slotSelect.value === "";
});

// Submit the selected appointment to the existing backend.
bookingForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const providerId = providerSelect.value;
    const date = dateInput.value;
    const selectedIndex = slotSelect.value;

    if (
        !providerId ||
        !date ||
        selectedIndex === "" ||
        !availableSlots[Number(selectedIndex)]
    ) {
        showStatus("Please select a provider, date, and time.", "error");
        return;
    }

    const selectedSlot = availableSlots[Number(selectedIndex)];

    const providerName =
        providerSelect.options[providerSelect.selectedIndex].textContent;

    const appointmentTime =
        `${formatTime(selectedSlot.start_time)} - ${formatTime(selectedSlot.end_time)}`;

    const confirmed = window.confirm(
        `Confirm your appointment?\n\n` +
        `Provider: ${providerName}\n` +
        `Date: ${date}\n` +
        `Time: ${appointmentTime}`
    );

    if (!confirmed) {
        return;
    }

    bookButton.disabled = true;
    bookButton.textContent = "Booking...";

    showStatus("Submitting your appointment...");

    const formData = new FormData();

    formData.append("provider_id", providerId);
    formData.append("availability_id", selectedSlot.availability_id);
    formData.append("appointment_date", date);
    formData.append("start_time", selectedSlot.start_time);
    formData.append("end_time", selectedSlot.end_time);

    try {
        const data = await fetchJson(API.create, {
            method: "POST",
            body: formData
        });

        showStatus(
            `Appointment successfully booked! Confirmation number: ${data.appointment_id}`,
            "success"
        );

        // Reload the slots so the newly booked time disappears.
        await loadSlots();

    } catch (error) {
        showStatus(error.message, "error");

        // Refresh availability in case another student booked the slot.
        await loadSlots();

    } finally {
        bookButton.textContent = "Book Appointment";
        bookButton.disabled = slotSelect.value === "";
    }
});

// Initial page setup.
resetSlots();
loadProviders();
</script>

</body>
</html>
