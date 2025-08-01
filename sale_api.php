<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'includes/auth.php';
require_once 'db/connection.php';


$response = ['success' => false];

// Function to fetch the latest sales list to refresh the page
function fetch_sales_data($conn) {
    $query = "SELECT s.s_id, s.date, c.c_name, p.p_name, p.category, p.type, p.unit, sd.qty, sd.price AS unit_price, sd.total_price
              FROM Sell s
              JOIN SellDetail sd ON s.s_id = sd.s_id
              JOIN Product p ON sd.p_id = p.p_id
              LEFT JOIN Customer c ON c.c_id = s.c_id
              ORDER BY s.s_id DESC";
    return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

try {
    $conn->begin_transaction();
    
    // Handle POST requests from the modal form
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON payload.');
        }
        $action = $payload['action'] ?? null;

        if ($action === 'add' || $action === 'edit') {
            $s_id = isset($payload['s_id']) ? intval($payload['s_id']) : 0;
            $date = $payload['date'];
            $customer_name = trim($payload['customer_name']);
            $product_name = trim($payload['product_name']);
            $qty = intval($payload['qty']);
            $emp_id = $_SESSION['user_id'] ?? 1;

            if (empty($date) || empty($customer_name) || empty($product_name) || $qty <= 0) {
                throw new Exception('Please fill in all fields correctly.');
            }

            // Find Product details (ID, Price, Stock)
            $stmt_prod = $conn->prepare("SELECT p_id, price, qty AS stock FROM Product WHERE p_name = ?");
            $stmt_prod->bind_param('s', $product_name);
            $stmt_prod->execute();
            $prod_result = $stmt_prod->get_result();            
            if ($prod_result->num_rows === 0) throw new Exception("Product '$product_name' not found.");
            $product = $prod_result->fetch_assoc();
            $p_id = $product['p_id'];
            
            // Use the unit price from the form if provided, otherwise use the product's price
            $unit_price = isset($payload['unit_price']) ? floatval($payload['unit_price']) : $product['price'];
            
            // Use the total price from the form if provided, otherwise calculate it
            $total_price = isset($payload['total_price']) ? floatval($payload['total_price']) : ($qty * $unit_price);
            
            // Find or Create Customer
            $stmt_cust = $conn->prepare("SELECT c_id FROM Customer WHERE c_name = ?");
            $stmt_cust->bind_param('s', $customer_name);
            $stmt_cust->execute();
            $cust_result = $stmt_cust->get_result();
            if ($cust_row = $cust_result->fetch_assoc()) {
                $c_id = $cust_row['c_id'];
            } else {
                $stmt_new_cust = $conn->prepare("INSERT INTO Customer (c_name) VALUES (?)");
                $stmt_new_cust->bind_param('s', $customer_name);
                $stmt_new_cust->execute();
                $c_id = $conn->insert_id;
            }

            // Total price is now calculated above with form values

           if ($action === 'edit') {
                if (!$s_id) throw new Exception("Sale ID is required for editing.");
                // Restore old stock
                $stmt_old_qty = $conn->prepare("SELECT p_id, qty FROM SellDetail WHERE s_id = ?");
                $stmt_old_qty->bind_param('i', $s_id);
                $stmt_old_qty->execute();
                $old_detail = $stmt_old_qty->get_result()->fetch_assoc();
                if ($old_detail) $conn->query("UPDATE Product SET qty = qty + {$old_detail['qty']} WHERE p_id = {$old_detail['p_id']}");
                
                // Update Sale
                $stmt_update_sell = $conn->prepare("UPDATE Sell SET c_id = ?, date = ? WHERE s_id = ?");
                $stmt_update_sell->bind_param('isi', $c_id, $date, $s_id);
                $stmt_update_sell->execute();
                
                // Update SellDetail with new quantity and prices
                $stmt_update_detail = $conn->prepare("UPDATE SellDetail SET p_id = ?, qty = ?, price = ?, total_price = ? WHERE s_id = ?");
                $stmt_update_detail->bind_param('iiddi', $p_id, $qty, $unit_price, $total_price, $s_id);
                $stmt_update_detail->execute();
            } else { // 'add' action
                if ($qty > $product['stock']) throw new Exception("Not enough stock for '{$product_name}'. Available: {$product['stock']}.");
                // Insert new Sale and Detail
                $stmt_sale = $conn->prepare("INSERT INTO Sell (c_id, emp_id, date, status) VALUES (?, ?, ?, 'unpaid')");
                $stmt_sale->bind_param('iis', $c_id, $emp_id, $date);
                $stmt_sale->execute();
                $s_id = $conn->insert_id;
                
                $stmt_detail = $conn->prepare("INSERT INTO SellDetail (s_id, p_id, qty, price, total_price) VALUES (?, ?, ?, ?, ?)");
                $stmt_detail->bind_param('iiidd', $s_id, $p_id, $qty, $unit_price, $total_price);
                $stmt_detail->execute();
            }
            
            // Deduct new stock
            $conn->query("UPDATE Product SET qty = qty - $qty WHERE p_id = $p_id");
        } elseif ($action === 'delete') { 
            $s_id = intval($payload['id'] ?? 0);
            if (!$s_id) throw new Exception("Sale ID required for deletion.");
            
            $stmt_old_qty = $conn->prepare("SELECT p_id, qty FROM SellDetail WHERE s_id = ?");
            $stmt_old_qty->bind_param('i', $s_id);
            $stmt_old_qty->execute();
            if($old_detail = $stmt_old_qty->get_result()->fetch_assoc()){
                $conn->query("UPDATE Product SET qty = qty + {$old_detail['qty']} WHERE p_id = {$old_detail['p_id']}");
            }
            
            $conn->query("DELETE FROM Payment WHERE s_id = $s_id");
            $conn->query("DELETE FROM SellDetail WHERE s_id = $s_id");
            $conn->query("DELETE FROM Sell WHERE s_id = $s_id");
        } else {
            throw new Exception("Invalid action specified.");
        }

        $conn->commit();
        $response['success'] = true;
        $response['sales'] = fetch_sales_data($conn);

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getProducts') {
        // Handle the GET request for populating the dropdown
        $products = $conn->query("SELECT p_id, p_name, qty, price FROM Product ORDER BY p_name ASC")->fetch_all(MYSQLI_ASSOC);

        // Send back products
        $response['products'] = $products;

    } else {
        throw new Exception('Invalid request.');
    }

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }

    $response['error'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>