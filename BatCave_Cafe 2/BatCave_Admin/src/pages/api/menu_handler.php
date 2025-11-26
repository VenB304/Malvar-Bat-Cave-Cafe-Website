<?php
require_once '../../includes/json_handler.php';

header('Content-Type: application/json');

$jsonHandler = new JsonHandler(__DIR__ . '/../../../../BatCave/src/data/menu.json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../../BatCave/src/images/menu/';
            if (!is_dir($uploadDir))
                mkdir($uploadDir, 0777, true);

            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = 'images/menu/' . $fileName;
            }
        }

        $newItem = [
            'name' => $_POST['name'] ?? 'New Item',
            'description' => $_POST['description'] ?? '',
            'price' => floatval($_POST['price'] ?? 0),
            'category' => $_POST['category'] ?? 'Uncategorized',
            'is_featured' => isset($_POST['is_featured']) ? true : false,
            'image' => $imagePath
        ];
        $id = $jsonHandler->append($newItem);
        echo json_encode(['success' => true, 'message' => 'Item added', 'id' => $id]);
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $currentData = $jsonHandler->read();
            $existingItem = null;
            foreach ($currentData as $item) {
                if ($item['id'] === $id) {
                    $existingItem = $item;
                    break;
                }
            }

            $imagePath = $existingItem['image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../../BatCave/src/images/menu/';
                if (!is_dir($uploadDir))
                    mkdir($uploadDir, 0777, true);

                $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Delete old image if exists
                    $sharedImagesDir = __DIR__ . '/../../../../BatCave/src/';
                    if (!empty($existingItem['image']) && file_exists($sharedImagesDir . $existingItem['image'])) {
                        unlink($sharedImagesDir . $existingItem['image']);
                    }
                    $imagePath = 'images/menu/' . $fileName;
                }
            }

            $updateData = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => floatval($_POST['price']),
                'category' => $_POST['category'],
                'is_featured' => isset($_POST['is_featured']) ? true : false,
                'image' => $imagePath
            ];
            $jsonHandler->update($id, $updateData);
            echo json_encode(['success' => true, 'message' => 'Item updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID required']);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            // Delete image file
            $currentData = $jsonHandler->read();
            foreach ($currentData as $item) {
                if ($item['id'] === $id && !empty($item['image'])) {
                    $sharedImagesDir = __DIR__ . '/../../../../BatCave/src/';
                    if (file_exists($sharedImagesDir . $item['image'])) {
                        unlink($sharedImagesDir . $item['image']);
                    }
                    break;
                }
            }
            $jsonHandler->delete($id);
            echo json_encode(['success' => true, 'message' => 'Item deleted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ID required']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} elseif ($method === 'GET') {
    echo json_encode($jsonHandler->read());
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
