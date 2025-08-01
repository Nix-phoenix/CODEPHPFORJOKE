<?php
header('Content-Type: application/json; charset=UTF-8');
require_once 'includes/auth.php';
require_once 'db/connection.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$action = $_GET['action'] ?? null;
$s_id = isset($_GET['sid']) ? intval($_GET['sid']) : 0;

try {
    $conn->begin_transaction();

    if ($action === 'toggle_status' && $s_id > 0) {
        // Check if a payment record exists for this sale ID
        $stmt_check = $conn->prepare("SELECT pm_id, status FROM Payment WHERE s_id = ?");
        $stmt_check->bind_param('i', $s_id);
        $stmt_check->execute();
        $result = $stmt_check->get_result();
        
        if ($payment = $result->fetch_assoc()) {
            // Payment exists, so update it
            $new_status = $payment['status'] === 'paid' ? 'unpaid' : 'paid';
            $stmt_update = $conn->prepare("UPDATE Payment SET status = ? WHERE pm_id = ?");
            $stmt_update->bind_param('si', $new_status, $payment['pm_id']);
            $stmt_update->execute();
        } else {
            // No payment exists, so create a new one with 'paid' status
            // First, calculate the total amount from SellDetail
            $stmt_total = $conn->prepare("SELECT SUM(total_price) as total FROM SellDetail WHERE s_id = ?");
            $stmt_total->bind_param('i', $s_id);
            $stmt_total->execute();
            $total_result = $stmt_total->get_result()->fetch_assoc();
            $total_amount = $total_result['total'] ?? 0;

            $stmt_insert = $conn->prepare("INSERT INTO Payment (s_id, amount, status, date) VALUES (?, ?, 'paid', NOW())");
            $stmt_insert->bind_param('id', $s_id, $total_amount);
            $stmt_insert->execute();
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

        // Delete associated records
        $conn->query("DELETE FROM Payment WHERE s_id = " . $s_id);
        $conn->query("DELETE FROM SellDetail WHERE s_id = " . $s_id);
        $conn->query("DELETE FROM Sell WHERE s_id = " . $s_id);

    } else {
        throw new Exception('Invalid action or missing Sale ID.');
    }

    $conn->commit();
    // Redirect back to the payments page to show the changes
    header('Location: payment.php?search=' . urlencode($_GET['search'] ?? ''));
    exit;

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    // You can create a simple error page or just display the error
    // For now, redirecting back with an error message in URL
    header('Location: payment.php?error=' . urlencode($e->getMessage()));
    exit;
}
?>
