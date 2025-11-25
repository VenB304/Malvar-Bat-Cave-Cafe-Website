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

            <div class="section-header admin-header" data-num="03">
                <h2 class="text-display-title mb-0">Admin Dashboard</h2>
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
                <div class="row admin-toolbar">
                    <p class="lead mb-0">Manage Menu Items</p>
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
            <div id="imagePreview" class="image-preview-container"></div>

            <label>
                <input type="checkbox" id="itemFeatured" name="is_featured"> Featured Item
            </label>

            <button type="submit" class="btn btn--primary u-full-width">Save Item</button>
        </form>
    </div>
</div>

<script src="js/admin.js"></script>

<?php include 'footer.php'; ?>