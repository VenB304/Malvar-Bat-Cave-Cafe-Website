<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'header.php';
?>

<!-- # admin
    ================================================== -->
<section id="admin" class="container s-admin target-section">

    <div class="row s-admin__content">

        <div class="column xl-12">

            <div class="section-header" data-num="03"
                style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="text-display-title" style="margin-bottom: 0;">Admin Dashboard</h2>
                <a href="logout.php" class="btn btn--stroke btn--small">Logout</a>
            </div>

            <!-- Admin Tabs -->
            <div class="admin-tabs">
                <button class="btn btn--primary tab-btn active" onclick="openTab('reservations')">Reservations</button>
                <button class="btn btn--stroke tab-btn" onclick="openTab('menu')">Menu Management</button>
            </div>

            <!-- Reservations Tab -->
            <div id="reservations" class="tab-content active">
                <p class="lead">Manage Reservation Requests</p>

                <div class="table-responsive">
                    <table class="u-full-width">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Mode</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reservationTable">
                            <!-- Data injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Menu Management Tab -->
            <div id="menu" class="tab-content" style="display:none;">
                <div class="row" style="justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <p class="lead" style="margin-bottom: 0;">Manage Menu Items</p>
                    <button class="btn btn--primary" onclick="openMenuModal()">Add New Item</button>
                </div>

                <div class="table-responsive">
                    <table class="u-full-width">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="menuTable">
                            <!-- Data injected by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

        </div> <!-- end column -->

    </div> <!-- end s-admin__content -->

</section> <!-- end s-admin -->

<!-- Menu Item Modal -->
<div id="menuModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" onclick="closeMenuModal()">&times;</span>
        <h3 id="menuModalTitle">Add Menu Item</h3>
        <form id="menuForm">
            <input type="hidden" id="itemId" name="id">
            <input type="hidden" id="formAction" name="action" value="add">

            <label for="itemName">Name</label>
            <input type="text" id="itemName" name="name" class="u-full-width" required>

            <label for="itemCategory">Category</label>
            <select id="itemCategory" name="category" class="u-full-width">
                <option value="Signature Coffee">Signature Coffee</option>
                <option value="Pastries">Pastries</option>
                <option value="Snacks">Snacks</option>
                <option value="Other">Other</option>
            </select>

            <label for="itemPrice">Price</label>
            <input type="number" id="itemPrice" name="price" class="u-full-width" step="0.01" required>

            <label for="itemDesc">Description</label>
            <textarea id="itemDesc" name="description" class="u-full-width"></textarea>

            <label for="itemImage">Image</label>
            <input type="file" id="itemImage" name="image" class="u-full-width" accept="image/*">
            <div id="imagePreview" style="margin-bottom: 1rem;"></div>

            <label>
                <input type="checkbox" id="itemFeatured" name="is_featured"> Featured Item
            </label>

            <button type="submit" class="btn btn--primary u-full-width">Save Item</button>
        </form>
    </div>
</div>

<style>
    .admin-tabs {
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--color-border);
        padding-bottom: 1rem;
    }

    .tab-btn {
        margin-right: 1rem;
    }

    .status-badge {
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-badge.pending {
        background-color: var(--color-notice);
        color: var(--color-notice-content);
    }

    .status-badge.approved {
        background-color: var(--color-success);
        color: var(--color-success-content);
    }

    .status-badge.rejected {
        background-color: var(--color-error);
        color: var(--color-error-content);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-content {
        background-color: var(--color-bg);
        margin: 10% auto;
        padding: 2rem;
        border: 1px solid var(--color-border);
        width: 90%;
        max-width: 600px;
        border-radius: var(--radius-md);
    }

    .close-modal {
        color: var(--color-text);
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .btn--small {
        height: auto;
        line-height: 1.5;
        padding: 0.2rem 0.8rem;
        font-size: 0.8rem;
        margin-right: 0.5rem;
    }

    .menu-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>

<script>
    // Tab Switching
    function openTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('btn--primary');
            el.classList.add('btn--stroke');
        });

        document.getElementById(tabName).style.display = 'block';
        event.target.classList.remove('btn--stroke');
        event.target.classList.add('btn--primary');

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

                    const row = `
                        <tr id="row-${item.id}">
                            <td>#${item.id}</td>
                            <td>${item.name}</td>
                            <td>${item.date}</td>
                            <td>${formatTime(item.time)} (${item.duration}hrs)</td>
                            <td>${item.mode} (${item.pax} Pax)</td>
                            <td><span class="status-badge ${statusClass}">${item.status}</span></td>
                            <td>
                                ${isPending ? `
                                    <button class="btn btn--primary btn--small" onclick="updateStatus('${item.id}', 'Approved')">Approve</button>
                                    <button class="btn btn--stroke btn--small" onclick="updateStatus('${item.id}', 'Rejected')">Reject</button>
                                ` : `
                                    <button class="btn btn--stroke btn--small" disabled>Locked</button>
                                `}
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
                                <div style="display:flex; align-items:center; gap:1rem;">
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
            document.getElementById('imagePreview').innerHTML = `<p>Current Image:</p><img src="${item.image}" style="max-width:100px; border-radius:4px;">`;
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

    document.getElementById('menuForm').addEventListener('submit', function (e) {
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

    // Initial Load
    loadReservations();
</script>

<?php include 'footer.php'; ?>