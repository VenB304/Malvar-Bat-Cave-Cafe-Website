<?php include '../../components/header.php'; ?>

<!-- # booking
    ================================================== -->
<section id="booking" class="container s-booking s-booking--flex target-section">

    <div class="row s-booking__content">

        <div class="column xl-12">

            <div class="section-header" data-num="01">
                <h2 class="text-display-title">Book The Cave</h2>
                <p class="lead">Select a date to check availability.</p>
            </div>

            <!-- Calendar Grid -->
            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevMonth" class="btn btn--stroke">&lt;</button>
                    <h3 id="currentMonthYear">November 2025</h3>
                    <button id="nextMonth" class="btn btn--stroke">&gt;</button>
                </div>
                <div class="calendar-grid" id="calendarGrid">
                    <!-- Days will be injected by JS -->
                </div>
                <div class="calendar-legend">
                    <span class="legend-item"><span class="dot green"></span> Available</span>
                    <span class="legend-item"><span class="dot yellow"></span> Partially Booked</span>
                    <span class="legend-item"><span class="dot red"></span> Full / Event</span>
                </div>
            </div>

        </div> <!-- end column -->

    </div> <!-- end s-booking__content -->

</section> <!-- end s-booking -->

<!-- Booking Modal -->
<div id="bookingModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3>Reserve for <span id="selectedDateDisplay"></span></h3>

        <form id="bookingForm">
            <input type="hidden" id="selectedDate" name="selectedDate">
            <div class="form-group">
                <label>Booking Mode</label>
                <div class="mode-toggle">
                    <label><input type="radio" name="mode" value="study" checked> Study Mode (₱50/hr/pax)</label>
                    <label><input type="radio" name="mode" value="event"> Event Mode (₱1000/hr)</label>
                </div>
            </div>
            <div class="row">
                <div class="column xl-6">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required class="u-full-width">
                </div>
                <div class="column xl-6">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="u-full-width">
                </div>
            </div>
            <div class="row">
                <div class="column xl-6">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" required class="u-full-width">
                </div>
                <div class="column xl-6" id="paxContainer">
                    <label for="pax">Pax (Max 20)</label>
                    <input type="number" id="pax" name="pax" min="1" max="20" value="1" class="u-full-width">
                </div>
            </div>
            <div class="row">
                <div class="column xl-4">
                    <label for="startTime">Start Time</label>
                    <select id="startTime" name="startTime" class="u-full-width"></select>
                </div>
                <div class="column xl-4">
                    <label for="duration">Duration (Hrs)</label>
                    <input type="number" id="duration" name="duration" min="1" max="12" value="1" class="u-full-width">
                </div>
            </div>
            <div class="form-group">
                <label>Equipment Add-ons</label>
                <label><input type="checkbox" id="projector" name="projector" value="150"> Projector (+₱150/hr)</label>
                <label><input type="checkbox" id="speaker" name="speaker" value="150"> Speaker &amp; Mic
                    (+₱150/hr)</label>
            </div>
            <div class="cost-estimation">
                <h4>Estimated Cost: <span id="totalCost">₱0.00</span></h4>
            </div>
            <button type="submit" class="btn btn--primary u-full-width btn--center">Submit
                Reservation</button>
        </form>
    </div>
</div>

<script src="../../scripts/booking.js"></script>

<?php include '../../components/footer.php'; ?>