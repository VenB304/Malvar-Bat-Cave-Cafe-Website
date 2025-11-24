document.addEventListener('DOMContentLoaded', function () {

    // --- Calendar Logic ---
    const calendarGrid = document.getElementById('calendarGrid');
    const currentMonthYear = document.getElementById('currentMonthYear');
    const prevMonthBtn = document.getElementById('prevMonth');
    const nextMonthBtn = document.getElementById('nextMonth');

    let currentDate = new Date();

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

            // Deterministic Availability: All days are marked as available (green) and clickable.
            dayCell.classList.add('green'); // Free
            dayCell.title = "Available";
            dayCell.onclick = () => openModal(year, month, i);

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

    renderCalendar(currentDate);


    // --- Modal & Form Logic ---
    const modal = document.getElementById('bookingModal');
    const closeModal = document.querySelector('.close-modal');
    const selectedDateInput = document.getElementById('selectedDate');
    const selectedDateDisplay = document.getElementById('selectedDateDisplay');
    const startTimeSelect = document.getElementById('startTime');

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
        const dateStr = new Date(year, month, day).toLocaleDateString();
        selectedDateInput.value = dateStr;
        selectedDateDisplay.textContent = dateStr;
        modal.style.display = "block";
        calculateCost(); // Reset cost
    }

    closeModal.onclick = function () {
        modal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }


    // --- Cost Estimation Logic ---
    const form = document.getElementById('bookingForm');
    const modeInputs = document.getElementsByName('mode');
    const durationInput = document.getElementById('duration');
    const paxInput = document.getElementById('pax');
    const paxContainer = document.getElementById('paxContainer');
    const projectorInput = document.getElementById('projector');
    const speakerInput = document.getElementById('speaker');
    const totalCostDisplay = document.getElementById('totalCost');

    function calculateCost() {
        let mode = document.querySelector('input[name="mode"]:checked').value;
        let duration = parseInt(durationInput.value) || 0;
        let pax = parseInt(paxInput.value) || 0;
        let total = 0;

        // Base Cost
        if (mode === 'study') {
            paxContainer.style.display = 'block';
            total = 50 * pax * duration;
        } else {
            paxContainer.style.display = 'none'; // Event is flat rate
            total = 1000 * duration;
        }

        // Equipment
        if (projectorInput.checked) total += 150 * duration;
        if (speakerInput.checked) total += 150 * duration;

        // Minimum Fee Rule
        if (total < 75 && total > 0) total = 75;

        totalCostDisplay.textContent = '₱' + total.toFixed(2);
    }

    // Event Listeners for Calculation
    form.addEventListener('change', calculateCost);
    form.addEventListener('input', calculateCost);

    // Form Submission (Mock)
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        alert('Reservation Request Submitted!\n\nTotal Estimated Cost: ' + totalCostDisplay.textContent);
        modal.style.display = "none";
        form.reset();
    });

});
