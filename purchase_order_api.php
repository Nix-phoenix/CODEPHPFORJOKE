<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
include 'includes/auth.php';
include 'db/connection.php';

header('Content-Type: application/json; charset=utf-8'); // Important: Set the correct Content-Type

$response = ['success' => false, 'error' => 'Invalid action'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null; // Use $_POST to retrieve data

    switch ($action) {
        case 'add':
        case 'edit':
            $sup_id = intval($_POST['supplierId']); // Use $_POST and correct field names
            $emp_id = intval($_POST['employeeId']);
            $p_id = intval($_POST['productId']);
            $qty = intval($_POST['orderQty']);
            $date = $_POST['orderDate'];
            $unitPrice = floatval($_POST['unitPrice']);

            if ($action === 'add') {
                $sql = "INSERT INTO PurchaseOrder (sup_id, emp_id, date) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iis', $sup_id, $emp_id, $date);
                if ($stmt->execute()) {
                    $po_id = $conn->insert_id;
                    $sql2 = "INSERT INTO PurchaseOrderDetail (po_id, p_id, qty, price) VALUES (?, ?, ?, ?)";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param('iiid', $po_id, $p_id, $qty, $unitPrice);
                    if ($stmt2->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Order added successfully';
                    } else {
                        $response['error'] = 'Failed to add order detail: ' . $stmt2->error;
                    }
                } else {
                    $response['error'] = 'Failed to add order: ' . $stmt->error;
                }
            } else {
                $id = intval($_POST['orderId']); // Use $_POST and correct field name
                $sql = "UPDATE PurchaseOrder SET sup_id = ?, emp_id = ?, date = ? WHERE po_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('iisi', $sup_id, $emp_id, $date, $id);
                if ($stmt->execute()) {
                    $sql2 = "UPDATE PurchaseOrderDetail SET p_id = ?, qty = ?, price = ? WHERE po_id = ?";
                    $stmt2 = $conn->prepare($sql2);
                    $stmt2->bind_param('iidi', $p_id, $qty, $unitPrice, $id);
                    if ($stmt2->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Order updated successfully';
                    } else {
                        $response['error'] = 'Failed to update order detail: ' . $stmt2->error;
                    }
                } else {
                    $response['error'] = 'Failed to update order: ' . $stmt->error;
                }
            }
            break;

        case 'delete':
            $id = intval($_POST['id']);
            $sql = "DELETE FROM PurchaseOrderDetail WHERE po_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $sql2 = "DELETE FROM PurchaseOrder WHERE po_id = ?";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param('i', $id);
                if ($stmt2->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Order deleted successfully';
                } else {
                    $response['error'] = 'Failed to delete order: ' . $stmt2->error;
                }
            } else {
                $response['error'] = 'Failed to delete order detail: ' . $stmt->error;
            }
            break;

        default:
            $response['error'] = 'Invalid action';
            break;
    }
} else {
    $response['error'] = 'Invalid request method';
}

// Fetch updated orders (moved outside the switch statement for consistency)
$stmt = $conn->prepare("SELECT po.po_id AS id, po.sup_id, po.emp_id, p.p_id, sup.sup_name AS supplier, emp.emp_name AS employee, p.p_name AS product, pod.qty, po.date, pod.price AS unitPrice FROM PurchaseOrder po JOIN Supplier sup ON po.sup_id=sup.sup_id JOIN Employee emp ON po.emp_id=emp.emp_id JOIN PurchaseOrderDetail pod ON pod.po_id=po.po_id JOIN Product p ON p.p_id=pod.p_id ORDER BY po.po_id DESC");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$response['orders'] = $orders;

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
