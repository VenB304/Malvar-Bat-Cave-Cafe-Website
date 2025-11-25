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
}

// Reservation Management
function loadReservations() {
    fetch('api/booking_handler.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('reservationTable');
            tbody.innerHTML = '';

            // Sort by date (newest first)
            data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            data.forEach(item => {
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
                        <td>${new Date(item.date).toDateString()}</td>
                        <td>${formatTime(item.time)} (${item.duration}hrs)</td>
                        <td>${item.mode} (${item.pax} Pax)</td>
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
        });
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
                const imageHtml = item.image ? `<img src="${item.image}" class="menu-thumb" alt="${item.name}">` : '<span style="color:#ccc;">No Image</span>';
                const row = `
                    <tr>
                        <td>
                            <div class="menu-item-header">
                                ${imageHtml}
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
        document.getElementById('imagePreview').innerHTML = `<p>Current Image:</p><img src="${item.image}" class="image-preview-thumb">`;
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
});
