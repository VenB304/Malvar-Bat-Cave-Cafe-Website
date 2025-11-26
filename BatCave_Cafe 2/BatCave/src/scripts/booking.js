document.addEventListener('DOMContentLoaded', function () {

    // --- Global State ---
    let bookings = [];

    // --- Fetch Bookings ---
    fetch('../api/booking_handler.php?action=availability')
        .then(response => response.json())
        .then(data => {
            bookings = data;
            renderCalendar(currentDate); // Re-render with data
        })
        .catch(err => console.error('Error fetching bookings:', err));

    // --- Calendar Logic ---
    const calendarGrid = document.getElementById('calendarGrid');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');

    let currentDate = new Date();

    function formatDate(year, month, day) {
        return `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    }

    function renderCalendar(date) {
        calendarGrid.innerHTML = '';
        const year = date.getFullYear();
        const month = date.getMonth();

        currentMonthYear.textContent = new Intl.DateTimeFormat('en-US', { month: 'long', year: 'numeric' }).format(date);

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        // Empty slots for previous month
        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            calendarGrid.appendChild(emptyCell);
        }

        // Days
        for (let i = 1; i <= daysInMonth; i++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('calendar-day');
            dayCell.textContent = i;

            const dateStr = formatDate(year, month, i);

            // Check availability
            const dayBookings = bookings.filter(b => b.date === dateStr);

            // Operating hours: 13 (1PM) to 25 (1AM next day) -> 12 slots
            // Slots: 0=13:00, 1=14:00, ... 11=24:00
            let hourlyPax = new Array(12).fill(0);
            let hourlyEvent = new Array(12).fill(false);
            let hasBookings = false;

            dayBookings.forEach(b => {
                // Only consider Approved or Pending bookings (assuming Pending blocks for now, or filter by Approved if desired)
                // If logic requires only Approved to block, add: if (b.status !== 'Approved') return;

                hasBookings = true;
                const startHour = parseInt(b.time);
                const duration = parseInt(b.duration);
                const startIndex = startHour - 13; // 13:00 is index 0

                for (let h = 0; h < duration; h++) {
                    const idx = startIndex + h;
                    if (idx >= 0 && idx < 12) {
                        if (b.mode === 'event') {
                            hourlyEvent[idx] = true;
                        } else {
                            hourlyPax[idx] += parseInt(b.pax);
                        }
                    }
                }
            });

            // Determine Day Status
            let fullyBookedHours = 0;

            for (let h = 0; h < 12; h++) {
                if (hourlyEvent[h] || hourlyPax[h] >= 20) {
                    fullyBookedHours++;
                }
            }

            if (fullyBookedHours === 12) {
                dayCell.classList.add('red'); // Fully Booked (Event or Full Capacity)
                dayCell.title = "Fully Booked";
                // Optional: Disable click if completely full, or allow click to see details?
                // User request implies "Full" prevents booking, but usually users want to see *why* or if *some* slots are open.
                // Given the "Full / Event" status description, we'll mark it red.
                // If strictly following "Cannot submit a booking" if full:
                dayCell.classList.add('disabled'); // Visual cue
                // dayCell.onclick = null; // To strictly prevent clicking
            } else if (hasBookings) {
                dayCell.classList.add('yellow'); // Partially Booked
                dayCell.title = "Partially Booked";
                dayCell.onclick = () => openModal(year, month, i);
            } else {
                dayCell.classList.add('green'); // Available
                dayCell.title = "Available";
                dayCell.onclick = () => openModal(year, month, i);
            }

            calendarGrid.appendChild(dayCell);
        }
    }

    prevMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar(currentDate);
    });

    nextMonthBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar(currentDate);
    });

    // Initial render (will be updated when fetch completes)
    renderCalendar(currentDate);


    // --- Modal & Form Logic ---
    const modal = document.getElementById('bookingModal');
    const closeModal = document.querySelector('.close-modal');
    const selectedDateInput = document.getElementById('selectedDate');
    const selectedDateDisplay = document.getElementById('selectedDateDisplay');
    const startTimeSelect = document.getElementById('startTime');

    // State for validation
    let currentDayHourlyPax = new Array(12).fill(0);
    let currentDayHourlyEvent = new Array(12).fill(false);

    // Populate Time Options (1 PM to 1 AM)
    const startHour = 13; // 1 PM
    const endHour = 25;   // 1 AM (next day)

    for (let i = startHour; i < endHour; i++) {
        const option = document.createElement('option');
        const hour = i % 24;
        const displayHour = hour > 12 ? hour - 12 : (hour === 0 || hour === 24 ? 12 : hour);
        const ampm = hour >= 12 && hour < 24 ? 'PM' : 'AM';

        option.value = i;
        option.textContent = `${displayHour}:00 ${ampm}`;
        startTimeSelect.appendChild(option);
    }

    function openModal(year, month, day) {
        const dateStr = formatDate(year, month, day);
        selectedDateInput.value = dateStr;
        selectedDateDisplay.textContent = new Date(year, month, day).toDateString();

        // Filter bookings for this day
        const dayBookings = bookings.filter(b => b.date === dateStr);

        // Reset and Calculate status
        currentDayHourlyPax.fill(0);
        currentDayHourlyEvent.fill(false);

        dayBookings.forEach(b => {
            const startHour = parseInt(b.time);
            const duration = parseInt(b.duration);
            const startIndex = startHour - 13;

            for (let h = 0; h < duration; h++) {
                const idx = startIndex + h;
                if (idx >= 0 && idx < 12) {
                    if (b.mode === 'event') {
                        currentDayHourlyEvent[idx] = true;
                    } else {
                        currentDayHourlyPax[idx] += parseInt(b.pax);
                    }
                }
            }
        });

        // Update Time Options
        Array.from(startTimeSelect.options).forEach(option => {
            const hour = parseInt(option.value);
            const idx = hour - 13;

            // Reset state
            option.disabled = false;
            let cleanText = option.textContent.split(' (')[0];
            const ampm = hour >= 12 && hour < 24 ? 'PM' : 'AM';
            const displayHour = hour > 12 ? hour - 12 : (hour === 0 || hour === 24 ? 12 : hour);
            option.textContent = `${displayHour}:00 ${ampm}`;

            if (idx >= 0 && idx < 12) {
                if (currentDayHourlyEvent[idx]) {
                    option.disabled = true;
                    option.textContent += ' (Event Booked)';
                } else if (currentDayHourlyPax[idx] >= 20) {
                    option.disabled = true;
                    option.textContent += ' (Full)';
                }
            }
        });

        // Select first available option
        const firstAvailable = Array.from(startTimeSelect.options).find(opt => !opt.disabled);
        if (firstAvailable) {
            startTimeSelect.value = firstAvailable.value;
        } else {
            startTimeSelect.value = "";
        }

        modal.style.display = "block";
        updateMaxPax(); // Initial update
        calculateCost();
    }

    closeModal.onclick = function () {
        modal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }


    // --- Cost Estimation & Validation Logic ---
    const form = document.getElementById('bookingForm');
    const modeInputs = document.getElementsByName('mode');
    const durationInput = document.getElementById('duration');
    const paxInput = document.getElementById('pax');
    const paxContainer = document.getElementById('paxContainer');
    const projectorInput = document.getElementById('projector');
    const speakerInput = document.getElementById('speaker');
    const totalCostDisplay = document.getElementById('totalCost');
    const paxLabel = document.querySelector('label[for="pax"]');

    function updateMaxPax() {
        const start = parseInt(startTimeSelect.value);
        const duration = parseInt(durationInput.value) || 1;
        const mode = document.querySelector('input[name="mode"]:checked').value;

        if (!start) return;

        let maxCapacity = 20;
        const startIndex = start - 13;

        // Check capacity for the entire duration
        for (let h = 0; h < duration; h++) {
            const idx = startIndex + h;
            if (idx >= 0 && idx < 12) {
                if (mode === 'study') {
                    // Remaining capacity = 20 - currentPax
                    const remaining = 20 - currentDayHourlyPax[idx];
                    if (remaining < maxCapacity) maxCapacity = remaining;
                } else {
                    // Event mode: exclusive.
                    if (currentDayHourlyPax[idx] > 0 || currentDayHourlyEvent[idx]) {
                        maxCapacity = 0; // Blocked
                    }
                }
            }
        }

        if (maxCapacity < 1) maxCapacity = 0;

        paxInput.max = maxCapacity;
        paxInput.value = Math.min(paxInput.value, maxCapacity);
        if (paxInput.value < 1 && maxCapacity > 0) paxInput.value = 1;

        paxLabel.textContent = `Pax (Max ${maxCapacity})`;

        const submitBtn = form.querySelector('button[type="submit"]');
        if (maxCapacity === 0) {
            submitBtn.disabled = true;
            submitBtn.textContent = "Unavailable for Selection";
        } else {
            submitBtn.disabled = false;
            submitBtn.textContent = "Submit Reservation";
        }
    }

    function calculateCost() {
        let mode = document.querySelector('input[name="mode"]:checked').value;
        let duration = parseInt(durationInput.value) || 0;
        let pax = parseInt(paxInput.value) || 0;
        let total = 0;

        // Base Cost
        if (mode === 'study') {
            total = 50 * pax * duration;
        } else {
            total = 1000 * duration;
        }
        paxContainer.style.display = 'block';

        // Equipment
        if (projectorInput.checked) total += 150 * duration;
        if (speakerInput.checked) total += 150 * duration;

        // Minimum Fee Rule
        if (total < 75 && total > 0) total = 75;

        totalCostDisplay.textContent = '₱' + total.toFixed(2);
    }

    // Event Listeners for Calculation & Validation
    form.addEventListener('change', () => {
        updateMaxPax();
        calculateCost();
    });
    form.addEventListener('input', () => {
        calculateCost();
    });

    startTimeSelect.addEventListener('change', updateMaxPax);
    durationInput.addEventListener('input', updateMaxPax);
    modeInputs.forEach(input => input.addEventListener('change', updateMaxPax));

    // Form Submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Final Validation
        const max = parseInt(paxInput.max);
        const val = parseInt(paxInput.value);
        if (val > max) {
            alert(`Cannot book more than ${max} pax for this time slot.`);
            return;
        }
        if (max === 0) {
            alert("Selected time slot is unavailable.");
            return;
        }

        const formData = new FormData(form);
        formData.append('action', 'create');
        formData.append('total_cost', totalCostDisplay.textContent.replace('₱', ''));

        fetch('../api/booking_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Reservation Request Submitted!\n\nYour Booking ID: ' + data.id + '\nTotal Estimated Cost: ' + totalCostDisplay.textContent);
                    modal.style.display = "none";
                    form.reset();
                    // Refresh bookings
                    fetch('../api/booking_handler.php?action=availability')
                        .then(res => res.json())
                        .then(d => {
                            bookings = d;
                            renderCalendar(currentDate);
                        });
                } else {
                    alert('Error submitting reservation: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    });

});
