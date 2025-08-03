<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'includes/auth.php';
require_once 'db/connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$action = $_GET['action'] ?? $_POST['action'] ?? null;
$s_id = isset($_GET['sid']) ? intval($_GET['sid']) : (isset($_POST['sid']) ? intval($_POST['sid']) : 0);

try {
    $conn->begin_transaction();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_customer_paid' && $s_id > 0) {
        $customer_paid = isset($_POST['customerPaid']) ? floatval($_POST['customerPaid']) : null;
        if ($customer_paid === null) {
            throw new Exception('Missing customer paid amount.');
        }

        // Check if a payment record already exists for this sale ID
        $stmt_check = $conn->prepare("SELECT pm_id FROM Payment WHERE s_id = ?");
        $stmt_check->bind_param('i', $s_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($payment = $result->fetch_assoc()) {
            // Payment exists, so update the customer_paid amount
            $stmt_update = $conn->prepare("UPDATE Payment SET customer_paid = ? WHERE pm_id = ?");
            $stmt_update->bind_param('di', $customer_paid, $payment['pm_id']);
            $stmt_update->execute();
            $stmt_update->close();
        } else {
            // No payment exists, so create a new one
            // First, calculate the total amount from SellDetail
            $stmt_total = $conn->prepare("SELECT SUM(total_price) as total FROM SellDetail WHERE s_id = ?");
            $stmt_total->bind_param('i', $s_id);
            $stmt_total->execute();
            $total_result = $stmt_total->get_result()->fetch_assoc();
            $total_amount = $total_result['total'] ?? 0;
            $stmt_total->close();

            $stmt_insert = $conn->prepare("INSERT INTO Payment (s_id, amount, customer_paid, date) VALUES (?, ?, ?, NOW())");
            $stmt_insert->bind_param('idd', $s_id, $total_amount, $customer_paid);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt_check->close();

    } elseif ($action === 'delete_sale' && $s_id > 0) {
        // This action deletes the entire sale and related records.
        // First, return the stock quantities
        $stmt_details = $conn->prepare("SELECT p_id, qty FROM SellDetail WHERE s_id = ?");
        $stmt_details->bind_param('i', $s_id);
        $stmt_details->execute();
        $details_result = $stmt_details->get_result();
        while ($row = $details_result->fetch_assoc()) {
            $conn->query("UPDATE Product SET qty = qty + " . intval($row['qty']) . " WHERE p_id = " . intval($row['p_id']));
        }
        $stmt_details->close();

        // Delete associated records
        $conn->query("DELETE FROM Payment WHERE s_id = " . $s_id);
        $conn->query("DELETE FROM SellDetail WHERE s_id = " . $s_id);
        $conn->query("DELETE FROM Sell WHERE s_id = " . $s_id);

    } else {
        throw new Exception('Invalid action or missing Sale ID.');
    }

    $conn->commit();
    header('Location: payment.php?search=' . urlencode($_GET['search'] ?? ''));
    exit;

} catch (Exception $e) {
    // Corrected to use mysqli::rollback() directly, as there's no inTransaction() method
    if (isset($conn)) {
      $conn->rollback();
    }
    header('Location: payment.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
