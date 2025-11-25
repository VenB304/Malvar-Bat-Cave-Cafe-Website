<?php
require_once '../includes/json_handler.php';

header('Content-Type: application/json');

$jsonHandler = new JsonHandler('../data/bookings.json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? 'create';

    if ($action === 'create') {
        $newBooking = [
            'name' => $_POST['name'] ?? 'Guest',
            'email' => $_POST['email'] ?? '',
            'date' => $_POST['date'] ?? '',
            'time' => $_POST['time'] ?? '',
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
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'list';
    $data = $jsonHandler->read();

    if ($action === 'availability') {
        $availability = [];
        foreach ($data as $booking) {
            if (($booking['status'] ?? '') !== 'Rejected') {
                $availability[] = [
                    'date' => $booking['date'] ?? '',
                    'time' => $booking['time'] ?? '',
                    'duration' => $booking['duration'] ?? 0,
                    'status' => $booking['status'] ?? ''
                ];
            }
        }
        echo json_encode($availability);
    } else {
        echo json_encode($data);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
