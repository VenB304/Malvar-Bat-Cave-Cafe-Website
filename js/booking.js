document.addEventListener('DOMContentLoaded', function () {

    // --- Global State ---
    let bookings = [];

    // --- Fetch Bookings ---
    fetch('api/booking_handler.php?action=availability')
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

            if (dayBookings.length > 0) {
                dayCell.classList.add('yellow'); // Partially Booked
                dayCell.title = "Partially Booked";
            } else {
                dayCell.classList.add('green'); // Free
                dayCell.title = "Available";
            }

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

    // Initial render (will be updated when fetch completes)
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
        const dateStr = formatDate(year, month, day);
        selectedDateInput.value = dateStr;
        selectedDateDisplay.textContent = new Date(year, month, day).toDateString();

        // Filter occupied hours
        const dayBookings = bookings.filter(b => b.date === dateStr);
        const occupiedHours = new Set();

        dayBookings.forEach(b => {
            const start = parseInt(b.time);
            const duration = parseInt(b.duration);
            for (let h = 0; h < duration; h++) {
                occupiedHours.add(start + h);
            }
        });

        // Update Time Options
        Array.from(startTimeSelect.options).forEach(option => {
            const hour = parseInt(option.value);
            // Reset
            option.disabled = false;
            option.textContent = option.textContent.replace(' (Booked)', '');

            if (occupiedHours.has(hour)) {
                option.disabled = true;
                option.textContent += ' (Booked)';
            }
        });

        // Select first available option
        const firstAvailable = Array.from(startTimeSelect.options).find(opt => !opt.disabled);
        if (firstAvailable) {
            startTimeSelect.value = firstAvailable.value;
        } else {
            startTimeSelect.value = ""; // All booked
        }

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

    // Event Listeners for Calculation
    form.addEventListener('change', calculateCost);
    form.addEventListener('input', calculateCost);

    // Form Submission
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        formData.append('action', 'create');
        formData.append('total_cost', totalCostDisplay.textContent.replace('₱', ''));

        fetch('api/booking_handler.php', {
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
                    fetch('api/booking_handler.php?action=availability')
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
