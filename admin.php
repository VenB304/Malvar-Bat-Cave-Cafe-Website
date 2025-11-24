<?php include 'header.php'; ?>

<!-- # admin
    ================================================== -->
<section id="admin" class="container s-admin target-section">

    <div class="row s-admin__content">

        <div class="column xl-12">

            <div class="section-header" data-num="03">
                <h2 class="text-display-title">Admin Dashboard</h2>
                <p class="lead">Manage Reservation Requests</p>
            </div>

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
                        <!-- Mock Data Row 1 -->
                        <tr id="row-1">
                            <td>#1001</td>
                            <td>John Doe</td>
                            <td>Nov 25, 2025</td>
                            <td>2:00 PM (2hrs)</td>
                            <td>Study (2 Pax)</td>
                            <td><span class="status-badge pending">Pending</span></td>
                            <td>
                                <button class="btn btn--primary btn--small"
                                    onclick="updateStatus(1, 'Approved')">Approve</button>
                                <button class="btn btn--stroke btn--small"
                                    onclick="updateStatus(1, 'Rejected')">Reject</button>
                            </td>
                        </tr>
                        <!-- Mock Data Row 2 -->
                        <tr id="row-2">
                            <td>#1002</td>
                            <td>Jane Smith</td>
                            <td>Nov 25, 2025</td>
                            <td>5:00 PM (3hrs)</td>
                            <td>Event</td>
                            <td><span class="status-badge approved">Approved</span></td>
                            <td>
                                <!-- No actions for already approved -->
                                <button class="btn btn--stroke btn--small" disabled>Locked</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div> <!-- end column -->

    </div> <!-- end s-admin__content -->

</section> <!-- end s-admin -->

<style>
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

    .btn--small {
        height: auto;
        line-height: 1.5;
        padding: 0.2rem 0.8rem;
        font-size: 0.8rem;
        margin-right: 0.5rem;
    }
</style>

<script>
    function updateStatus(rowId, status) {
        const row = document.getElementById('row-' + rowId);
        const badge = row.querySelector('.status-badge');
        const buttons = row.querySelectorAll('button');

        badge.textContent = status;
        badge.className = 'status-badge ' + status.toLowerCase();

        // Disable buttons to simulate "Saved" state
        buttons.forEach(btn => btn.disabled = true);

        alert('Reservation #' + (1000 + rowId) + ' marked as ' + status);
    }
</script>

<?php include 'footer.php'; ?>