<?php
include 'includes/auth.php';
include 'db/connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => null, 'products' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action'])) {
        $action = $input['action'];

        switch ($action) {
            case 'add':
                $name = $input['name'];
                $category = $input['category'];
                $type = $input['type'];
                $shelf = $input['shelf'];
                $unit = $input['unit'];
                $qty = $input['qty'];
                $price = $input['price'];

                $stmt = $conn->prepare("INSERT INTO Product (p_name, category, type, shelf, unit, qty, price) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssii", $name, $category, $type, $shelf, $unit, $qty, $price);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $stmt = $conn->prepare("SELECT p_id AS id, p_name AS name, category, type, shelf, unit, qty, price FROM Product ORDER BY p_id ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $response['products'] = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                } else {
                    $response['error'] = "Database error: " . $stmt->error;
                }
                break;

            case 'edit':
                $id = $input['id'];
                $name = $input['name'];
                $category = $input['category'];
                $type = $input['type'];
                $shelf = $input['shelf'];
                $unit = $input['unit'];
                $qty = $input['qty'];
                $price = $input['price'];

                $stmt = $conn->prepare("UPDATE Product SET p_name=?, category=?, type=?, shelf=?, unit=?, qty=?, price=? WHERE p_id=?");
                $stmt->bind_param("sssssiii", $name, $category, $type, $shelf, $unit, $qty, $price, $id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $stmt = $conn->prepare("SELECT p_id AS id, p_name AS name, category, type, shelf, unit, qty, price FROM Product ORDER BY p_id ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $response['products'] = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                } else {
                    $response['error'] = "Database error: " . $stmt->error;
                }
                break;

            case 'delete':
                $id = $input['id'];

                // Check if product is referenced in SellDetail or PurchaseOrderDetail
                $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM SellDetail WHERE p_id = ?");
                $checkStmt->bind_param("i", $id);
                $checkStmt->execute();
                $sellCount = $checkStmt->get_result()->fetch_assoc()['count'];

                $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM PurchaseOrderDetail WHERE p_id = ?");
                $checkStmt->bind_param("i", $id);
                $checkStmt->execute();
                $purchaseCount = $checkStmt->get_result()->fetch_assoc()['count'];
                $checkStmt->close();

                if ($sellCount > 0 || $purchaseCount > 0) {
                    $response['error'] = "Cannot delete product: It is referenced in " .
                        ($sellCount > 0 ? "$sellCount sale(s)" : "") .
                        ($sellCount > 0 && $purchaseCount > 0 ? " and " : "") .
                        ($purchaseCount > 0 ? "$purchaseCount purchase order(s)" : "") . ".";
                    break;
                }

                $stmt = $conn->prepare("DELETE FROM Product WHERE p_id=?");
                $stmt->bind_param("i", $id);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $stmt = $conn->prepare("SELECT p_id AS id, p_name AS name, category, type, shelf, unit, qty, price FROM Product ORDER BY p_id ASC");
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $response['products'] = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();
                } else {
                    $response['error'] = "Database error: " . $stmt->error;
                }
                break;

            default:
                $response['error'] = "Invalid action";
                break;
        }
    } else {
        $response['error'] = "Action not specified";
    }
} else {
    $response['error'] = "Invalid request method";
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
