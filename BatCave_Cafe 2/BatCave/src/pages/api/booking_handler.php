<?php
require_once '../../includes/json_handler.php';

header('Content-Type: application/json');

$jsonHandler = new JsonHandler(__DIR__ . '/../../data/bookings.json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $newBooking = [
            'id' => uniqid(), // Ensure ID is generated
            'name' => $_POST['name'] ?? 'Guest',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'date' => $_POST['selectedDate'] ?? '',
            'time' => $_POST['startTime'] ?? '',
            'mode' => $_POST['mode'] ?? 'study',
            'pax' => intval($_POST['pax'] ?? 1),
            'duration' => intval($_POST['duration'] ?? 1),
            'projector' => isset($_POST['projector']) ? true : false,
            'speaker' => isset($_POST['speaker']) ? true : false,
            'total_cost' => $_POST['total_cost'] ?? 0,
            'status' => 'Pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $jsonHandler->append($newBooking);
        echo json_encode(['success' => true, 'message' => 'Booking submitted', 'id' => $id]);
    } elseif ($action === 'update_status') {
        $id = $_POST['id'] ?? null;
        $status = $_POST['status'] ?? null;

        if ($id && $status) {
            $jsonHandler->update($id, ['status' => $status]);
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID and Status required']);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $jsonHandler->delete($id);
            echo json_encode(['success' => true, 'message' => 'Reservation deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID required']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    $data = $jsonHandler->read();

    if ($action === 'availability') {
        // Return full data for client-side calculation, excluding rejected
        $activeBookings = array_filter($data, function ($b) {
            return ($b['status'] ?? '') !== 'Rejected';
        });
        echo json_encode(array_values($activeBookings));
    } else {
        echo json_encode($data);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
