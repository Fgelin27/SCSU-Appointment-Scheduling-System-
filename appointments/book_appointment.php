<?php
session_start();
if (
    empty($_SESSION['accountID']) || strtolower(trim($_SESSION['role'] ?? '')) !== 'student'
){
    header('Location: ../account_login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Book Appointment </title>

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
    <h1>Book an Appointment</h1>
    <p>Select a provider and appointment date to view available appointment times.</p>
    <section class="card provider-card">
        <form id="bookingForm">
            <div class="form-grid booking-form-grid">
                <div class="form-group">
                    <h2 for="provider">Select Provider</h2>
                    <select id="provider" name="provider_id" required>
                        <option value="">Loading providers...</option>
                    </select>
                </div>
                
                <div class="form-group new-row">
                    <h2 for="appointmentDate">Appoinment Date</h2>
                    <input type="date" id="appointmentDate" name="appointment_date" required>
                </div>
                <div class="form-group new-row">
                    <h2 for="appointmentSlot">Available Time</h2>
                    <select id="appointmentSlot" name="appointment_slot" required disabled>
                        <option value="">Select a provider and date first</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit id="bookButton class="primary-button" disabled>
                    Book Appoinment</button>
            </div>

            <div id="bookingStatus" class="status=message" role="status" aria-live="polite">
            </did>
</main>

<script>
    const API ={
        providers: "get_providers.php",
        slots: "get_bookable_slots.php",
        create: "create_appointment.php"
    };
    const providerSelect = document.getElementById("provider");
    const dateInput = document.getElementById("appoinmentDate");
    const slotSelect = document.getElementById("appointmentSlot");
    const bookingForm = document.getElementById("bookingForm");
    const bookButton = document.getElementById("bookButton");
    const slotSelect = document.getElementById("slotSelect");

    let availableSlots = [];
    let slotRequestNumber = 0;

    //Usage of localTime
    function getLocalDate(){
        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth()+1).padStart(2,"0");
        const day = String(now.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    }

    dateInput.min = getLocalDate();
    function showStatus(message, type=""){
        statusMessage.textContent = message;
        statusMessage.className = "status-message";
        if(type){
            statusMessage.classList.add(type);
        }
    }
    function resetSlots(message = "Select a provider and date first"){
        availableSlots=[];
        slotSelect.innerHTML="";
        const option = document.createElement("option");
        option.value="";
        option.textContent = message;
        slotSelect.appendChild(option);
        slotSelect.disabled = true;
        bookButton.disabled = true;
    }
    async function fetchJson(url, options={}){
        const response = await fetch(url, {
            ...options,
            cache: "no-store"
        });
        let data;
        try {
            data = await response.json();
        }catch{
            trow new Error("The server returned an invalid response");
        }
        return data;
    }

    //retrieve all providers

    async function loadProviders(){
        providerSelect.disabled=true;
        slotSelect.innerHTML= '<option value="">Loading providers...</option>';
        try{
            const data = await fetchJson(API.providers);
            providerSelect.innerHTML = '<option value="">No providers available</option>';
            showStatus("There are currently no providers available.", "error");
            return;
        }
        const placeholder = document.createElement("option");
        placeholder.value-"";
        placeholder.textContent="Choose a provider";
        providerSelect.appendChild(placeholder);
        








                



                        