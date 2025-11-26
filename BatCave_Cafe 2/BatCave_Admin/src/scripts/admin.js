// Tab Switching
function openTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('btn--primary');
        el.classList.add('btn--stroke');
    });

    document.getElementById(tabName).style.display = 'block';
    // Check if event is defined (it might not be if called programmatically without event)
    if (window.event) {
        window.event.target.classList.remove('btn--stroke');
        window.event.target.classList.add('btn--primary');
    } else {
        // Fallback or handle initial load if needed, though initial load usually doesn't pass event
        // For initial load, the HTML has 'active' class
    }

    if (tabName === 'menu') loadMenu();
    if (tabName === 'reservations') loadReservations();
    if (tabName === 'announcements') loadAnnouncements();
}

// Reservation Management
let allReservations = [];
let currentPage = 1;
let rowsPerPage = 10;

function loadReservations() {
    fetch('api/booking_handler.php')
        .then(response => response.json())
        .then(data => {
            // Sort by date (newest first)
            data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            allReservations = data;

            // Load rowsPerPage from cookie
            const savedRows = getCookie('adminRowsPerPage');
            if (savedRows) {
                rowsPerPage = parseInt(savedRows);
                const dropdown = document.getElementById('rowsPerPage');
                if (dropdown) dropdown.value = rowsPerPage;
            }

            currentPage = 1;
            renderReservations();
        });
}

function renderReservations() {
    const tbody = document.getElementById('reservationTable');
    tbody.innerHTML = '';

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    const paginatedItems = allReservations.slice(start, end);

    paginatedItems.forEach(item => {
        const statusClass = item.status.toLowerCase();
        const isPending = item.status === 'Pending';
        const isApproved = item.status === 'Approved';

        const row = `
            <tr id="row-${item.id}">
                <td>#${item.id.substring(0, 8)}...</td>
                <td>
                    <strong>${item.name}</strong><br>
                    <span style="font-size: 0.8em; color: #888;">${item.email}</span>
                </td>
                <td>${formatAdminDate(item.date)}</td>
                <td>${formatTime(item.time)} (${item.duration}hrs)</td>
                <td>${capitalizeFirstLetter(item.mode)} (${item.pax} Pax)</td>
                <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                <td>
                    ${isPending ? `
                        <button class="btn btn--primary btn--small" onclick="updateStatus('${item.id}', 'Approved')">Approve</button>
                        <button class="btn btn--stroke btn--small" onclick="updateStatus('${item.id}', 'Rejected')">Reject</button>
                    ` : ''}
                    ${isApproved ? `
                        <button class="btn btn--stroke btn--small" onclick="updateStatus('${item.id}', 'Pending')">Unapprove</button>
                    ` : ''}
                    <button class="btn btn--stroke btn--small" style="color: var(--color-error); border-color: var(--color-error);" onclick="deleteReservation('${item.id}')">Delete</button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });

    updatePaginationControls();
}

function formatAdminDate(dateString) {
    const date = new Date(dateString);
    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    const dayName = days[date.getDay()];
    const monthName = months[date.getMonth()];
    const day = date.getDate();
    const year = date.getFullYear();

    return `${dayName} - ${monthName}, ${day} ${year}`;
}

function capitalizeFirstLetter(string) {
    if (!string) return '';
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function updatePaginationControls() {
    const totalPages = Math.ceil(allReservations.length / rowsPerPage) || 1;
    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;

    document.getElementById('prevPageBtn').disabled = currentPage === 1;
    document.getElementById('nextPageBtn').disabled = currentPage === totalPages;
}

function changePage(delta) {
    const totalPages = Math.ceil(allReservations.length / rowsPerPage) || 1;
    const newPage = currentPage + delta;

    if (newPage >= 1 && newPage <= totalPages) {
        currentPage = newPage;
        renderReservations();
    }
}

function updateRowsPerPage() {
    rowsPerPage = parseInt(document.getElementById('rowsPerPage').value);
    setCookie('adminRowsPerPage', rowsPerPage, 30); // Save for 30 days
    currentPage = 1;
    renderReservations();
}

// Cookie Helpers
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', id);
    formData.append('status', status);

    fetch('api/booking_handler.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadReservations();
            } else {
                alert('Error updating status');
            }
        });
}

function deleteReservation(id) {
    if (confirm('Are you sure you want to PERMANENTLY delete this reservation?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('api/booking_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadReservations();
                } else {
                    alert('Error deleting reservation: ' + data.message);
                }
            });
    }
}

function formatTime(hour) {
    hour = parseInt(hour);
    const ampm = hour >= 12 && hour < 24 ? 'PM' : 'AM';
    const displayHour = hour > 12 ? hour - 12 : (hour === 0 || hour === 24 ? 12 : hour);
    return `${displayHour}:00 ${ampm}`;
}

// Menu Management
function loadMenu() {
    fetch('api/menu_handler.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('menuTable');
            tbody.innerHTML = '';
            data.forEach(item => {
                const imageHtml = item.image ? `<img src="../../../../BatCave/src/${item.image}" class="menu-thumb" alt="${item.name}">` : '<span style="color:#ccc;">No Image</span>';
                const row = `
                    <tr>
                        <td>${imageHtml}</td>
                        <td>
                            <div class="menu-item-header">
                                <span>${item.name}</span>
                            </div>
                        </td>
                        <td>${item.category}</td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                        <td>${item.is_featured ? 'Yes' : 'No'}</td>
                        <td>
                            <button class="btn btn--small btn--stroke" onclick='editItem(${JSON.stringify(item)})'>Edit</button>
                            <button class="btn btn--small btn--stroke" onclick="deleteItem('${item.id}')">Delete</button>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        });
}

function openMenuModal() {
    document.getElementById('menuForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('menuModalTitle').innerText = 'Add Menu Item';
    document.getElementById('imagePreview').innerHTML = '';
    document.getElementById('menuModal').style.display = 'block';
}

function closeMenuModal() {
    document.getElementById('menuModal').style.display = 'none';
}

function editItem(item) {
    document.getElementById('itemId').value = item.id;
    document.getElementById('itemName').value = item.name;
    document.getElementById('itemCategory').value = item.category;
    document.getElementById('itemPrice').value = item.price;
    document.getElementById('itemDesc').value = item.description;
    document.getElementById('itemFeatured').checked = item.is_featured;
    document.getElementById('formAction').value = 'edit';
    document.getElementById('menuModalTitle').innerText = 'Edit Menu Item';

    if (item.image) {
        document.getElementById('imagePreview').innerHTML = `<p>Current Image:</p><img src="../../../../BatCave/src/${item.image}" class="image-preview-thumb">`;
    } else {
        document.getElementById('imagePreview').innerHTML = '';
    }

    document.getElementById('menuModal').style.display = 'block';
}

function deleteItem(id) {
    if (confirm('Are you sure you want to delete this item?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('api/menu_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) loadMenu();
                else alert('Error deleting item');
            });
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function () {
    const menuForm = document.getElementById('menuForm');
    if (menuForm) {
        menuForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api/menu_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeMenuModal();
                        loadMenu();
                    } else {
                        alert('Error saving item');
                    }
                });
        });
    }

    // Initial Load
    // Only load if elements exist to avoid errors on other pages if included globally (though it's specific to admin)
    if (document.getElementById('reservationTable')) {
        loadReservations();
    }

    const announcementForm = document.getElementById('announcementForm');
    if (announcementForm) {
        announcementForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api/announcement_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        closeAnnouncementModal();
                        loadAnnouncements();
                    } else {
                        alert('Error saving announcement');
                    }
                });
        });
    }
});

// Announcement Management
let allAnnouncements = [];
let currentAnnouncementPage = 1;
let announcementRowsPerPage = 10;

function loadAnnouncements() {
    fetch('api/announcement_handler.php')
        .then(response => response.json())
        .then(data => {
            // Sort by date (newest first)
            data.sort((a, b) => new Date(b.date) - new Date(a.date));
            allAnnouncements = data;

            currentAnnouncementPage = 1;
            renderAnnouncements();
        });
}

function renderAnnouncements() {
    const tbody = document.getElementById('announcementTable');
    tbody.innerHTML = '';

    const start = (currentAnnouncementPage - 1) * announcementRowsPerPage;
    const end = start + announcementRowsPerPage;
    const paginatedItems = allAnnouncements.slice(start, end);

    paginatedItems.forEach(item => {
        const imageHtml = item.image ? `<img src="../../../../BatCave/src/${item.image}" class="menu-thumb" alt="${item.title}">` : '<span style="color:#ccc;">No Image</span>';
        const row = `
            <tr>
                <td>${imageHtml}</td>
                <td>
                    <strong>${item.title}</strong>
                </td>
                <td>${formatAdminDate(item.date)}</td>
                <td>
                    <button class="btn btn--small btn--stroke" onclick='editAnnouncement(${JSON.stringify(item)})'>Edit</button>
                    <button class="btn btn--small btn--stroke" onclick="deleteAnnouncement('${item.id}')">Delete</button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });

    updateAnnouncementPaginationControls();
}

function updateAnnouncementPaginationControls() {
    const totalPages = Math.ceil(allAnnouncements.length / announcementRowsPerPage) || 1;
    document.getElementById('announcementPageInfo').textContent = `Page ${currentAnnouncementPage} of ${totalPages}`;

    document.getElementById('prevAnnouncementPageBtn').disabled = currentAnnouncementPage === 1;
    document.getElementById('nextAnnouncementPageBtn').disabled = currentAnnouncementPage === totalPages;
}

function changeAnnouncementPage(delta) {
    const totalPages = Math.ceil(allAnnouncements.length / announcementRowsPerPage) || 1;
    const newPage = currentAnnouncementPage + delta;

    if (newPage >= 1 && newPage <= totalPages) {
        currentAnnouncementPage = newPage;
        renderAnnouncements();
    }
}

function updateAnnouncementRowsPerPage() {
    announcementRowsPerPage = parseInt(document.getElementById('announcementRowsPerPage').value);
    currentAnnouncementPage = 1;
    renderAnnouncements();
}

function openAnnouncementModal() {
    document.getElementById('announcementForm').reset();
    document.getElementById('announcementFormAction').value = 'add';
    document.getElementById('announcementModalTitle').innerText = 'Add Announcement';
    document.getElementById('announcementImagePreview').innerHTML = '';

    // Set default date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('announcementDate').value = today;

    document.getElementById('announcementModal').style.display = 'block';
}

function closeAnnouncementModal() {
    document.getElementById('announcementModal').style.display = 'none';
}

function editAnnouncement(item) {
    document.getElementById('announcementId').value = item.id;
    document.getElementById('announcementTitle').value = item.title;
    document.getElementById('announcementDate').value = item.date;
    document.getElementById('announcementContent').value = item.content;
    document.getElementById('announcementFormAction').value = 'edit';
    document.getElementById('announcementModalTitle').innerText = 'Edit Announcement';

    if (item.image) {
        document.getElementById('announcementImagePreview').innerHTML = `<p>Current Image:</p><img src="../../../../BatCave/src/${item.image}" class="image-preview-thumb">`;
    } else {
        document.getElementById('announcementImagePreview').innerHTML = '';
    }

    document.getElementById('announcementModal').style.display = 'block';
}

function deleteAnnouncement(id) {
    if (confirm('Are you sure you want to delete this announcement?')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('api/announcement_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) loadAnnouncements();
                else alert('Error deleting announcement');
            });
    }
}
