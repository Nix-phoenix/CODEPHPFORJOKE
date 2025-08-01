<?php
include 'includes/auth.php';
include 'db/connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => null, 'products' => []];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? null;

        if (!$action) {
            $response['error'] = "Action not specified.";
        } else {
            // Check if the uploads directory exists, if not, create it
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            switch ($action) {
                case 'add':
                    $name = $_POST['productName'];
                    $category = $_POST['productCategory'];
                    $type = $_POST['productType'];
                    $shelf = $_POST['productShelf'];
                    $unit = $_POST['productUnit'];
                    $qty = (int)$_POST['productQty'];
                    $price = (float)$_POST['productPrice'];
                    $image_path = '';

                    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
                        $file_tmp_name = $_FILES['productImage']['tmp_name'];
                        $file_name = uniqid('img_') . '_' . basename($_FILES['productImage']['name']);
                        $image_path = $upload_dir . $file_name;
                        
                        if (!move_uploaded_file($file_tmp_name, $image_path)) {
                            throw new Exception("Failed to upload image.");
                        }
                    }

                    $stmt = $conn->prepare("INSERT INTO Product (p_name, category, type, shelf, unit, qty, price, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssids", $name, $category, $type, $shelf, $unit, $qty, $price, $image_path);

                    if ($stmt->execute()) {
                        $response['success'] = true;
                    } else {
                        $response['error'] = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                    break;

                case 'edit':
                    $id = (int)$_POST['productId'];
                    $name = $_POST['productName'];
                    $category = $_POST['productCategory'];
                    $type = $_POST['productType'];
                    $shelf = $_POST['productShelf'];
                    $unit = $_POST['productUnit'];
                    $qty = (int)$_POST['productQty'];
                    $price = (float)$_POST['productPrice'];
                    
                    $old_image_path = null;
                    $new_image_path = '';

                    // Handle new image upload
                    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
                        // Get the current image path from the database to be deleted later
                        $stmt_select = $conn->prepare("SELECT image_path FROM Product WHERE p_id = ?");
                        $stmt_select->bind_param("i", $id);
                        $stmt_select->execute();
                        $result_select = $stmt_select->get_result();
                        $product = $result_select->fetch_assoc();
                        $old_image_path = $product['image_path'] ?? null;
                        $stmt_select->close();

                        // Save the new image
                        $file_tmp_name = $_FILES['productImage']['tmp_name'];
                        $file_name = uniqid('img_') . '_' . basename($_FILES['productImage']['name']);
                        $new_image_path = $upload_dir . $file_name;
                        
                        if (!move_uploaded_file($file_tmp_name, $new_image_path)) {
                            throw new Exception("Failed to upload new image.");
                        }
                    }

                    $sql = "UPDATE Product SET p_name=?, category=?, type=?, shelf=?, unit=?, qty=?, price=?";
                    if ($new_image_path !== '') {
                        $sql .= ", image_path=?";
                    }
                    $sql .= " WHERE p_id=?";
                    $stmt = $conn->prepare($sql);

                    if ($new_image_path !== '') {
                        $stmt->bind_param("sssssidsi", $name, $category, $type, $shelf, $unit, $qty, $price, $new_image_path, $id);
                    } else {
                        $stmt->bind_param("sssssidi", $name, $category, $type, $shelf, $unit, $qty, $price, $id);
                    }

                    if ($stmt->execute()) {
                        $response['success'] = true;
                        // Delete the old image file if a new one was uploaded
                        if ($new_image_path !== '' && $old_image_path && file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    } else {
                        $response['error'] = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                    break;
                
                case 'delete':
                    $id = (int)$_POST['id'];

                    // Check if product is referenced in SellDetail or PurchaseOrderDetail
                    $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM SellDetail WHERE p_id = ?");
                    $checkStmt->bind_param("i", $id);
                    $checkStmt->execute();
                    $sellCount = $checkStmt->get_result()->fetch_assoc()['count'];
                    $checkStmt->close();

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

                    // Get image path to delete file from the server
                    $stmt_select = $conn->prepare("SELECT image_path FROM Product WHERE p_id = ?");
                    $stmt_select->bind_param("i", $id);
                    $stmt_select->execute();
                    $result_select = $stmt_select->get_result();
                    $product = $result_select->fetch_assoc();
                    $image_path = $product['image_path'] ?? '';
                    $stmt_select->close();

                    $stmt = $conn->prepare("DELETE FROM Product WHERE p_id=?");
                    $stmt->bind_param("i", $id);

                    if ($stmt->execute()) {
                        $response['success'] = true;
                        // Delete the image file from the server
                        if ($image_path !== '' && file_exists($image_path)) {
                            unlink($image_path);
                        }
                    } else {
                        $response['error'] = "Database error: " . $stmt->error;
                    }
                    $stmt->close();
                    break;
                
                default:
                    $response['error'] = "Invalid action.";
                    break;
            }
        }
    } else {
        $response['error'] = "Invalid request method.";
    }

    // Re-fetch all products to return the updated list
    if ($response['success']) {
        $stmt = $conn->prepare("SELECT p_id AS id, p_name AS name, category, type, shelf, unit, qty, price, image_path FROM Product ORDER BY p_id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $response['products'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
$conn->close();
?>