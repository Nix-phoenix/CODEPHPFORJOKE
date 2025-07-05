<?php
include 'includes/auth.php';
include 'db/connection.php';

// Fetch order and payment info from the database
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = '';
if ($search !== '') {
    $search_esc = $conn->real_escape_string($search);
    $search_sql = "WHERE s.s_id LIKE '%$search_esc%' OR c.c_name LIKE '%$search_esc%' OR p.p_name LIKE '%$search_esc%'";
}
$orders = $conn->query("
    SELECT 
        s.s_id,
        s.c_id,
        c.c_name,
        sd.p_id,
        p.p_name,
        sd.qty,
        sd.price,
        sd.total_price,
        s.status AS order_status,
        pm.status AS payment_status,
        pm.date AS payment_date
    FROM Sell s
    JOIN Customer c ON s.c_id = c.c_id
    JOIN SellDetail sd ON s.s_id = sd.s_id
    JOIN Product p ON sd.p_id = p.p_id
    LEFT JOIN Payment pm ON pm.s_id = s.s_id
    $search_sql
    ORDER BY s.s_id DESC
    LIMIT 20
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ordering & Payment - Store Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <h2 class="dashboard-title" style="margin-bottom: 18px;">ການສັ່ງຊື້ & ການຊຳລະ</h2>
        <form method="get" style="display:flex;gap:12px;margin-bottom:18px;">
            <input type="text" name="search" placeholder="ຄົ້ນຫາ (ລູກຄ້າ, ສິນຄ້າ, ເລກທີ)" value="<?php echo htmlspecialchars($search); ?>" style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="dashboard-add-btn" style="width:180px;">ຄົ້ນຫາ</button>
        </form>
        <table class="dashboard-table order-table-improved">
            <thead>
                <tr>
                    <th>ເລກທີຄຳສັ່ງ</th>
                    <th>ຊື່ລູກຄ້າ</th>
                    <th>ສິນຄ້າ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາລວມ</th>
                    <th>ສະຖານະ</th>
                    <th>ສະຖານະການຈ່າຍ</th>
                    <th>ວັນທີຈ່າຍ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders->num_rows === 0): ?>
                <tr><td colspan="9" style="text-align:center;color:#888;">ບໍ່ມີຂໍ້ມູນການສັ່ງຊື້</td></tr>
                <?php else: ?>
                <?php $rowIdx = 0; while($row = $orders->fetch_assoc()): $rowIdx++; ?>
                <tr class="<?php echo $rowIdx % 2 == 0 ? 'even-row' : 'odd-row'; ?>">
                    <td><?php echo htmlspecialchars($row['s_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['c_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['qty']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_price']); ?> kip</td>
                    <td>
                        <?php if ($row['order_status'] == 'paid'): ?>
                            <span class="badge badge-success">ຈ່າຍເງິນແລ້ວ</span>
                        <?php else: ?>
                            <span class="badge badge-danger">ຍັງບໍ່ຈ່າຍ</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['payment_status'] == 'paid'): ?>
                            <span class="badge badge-success">ສຳເລັດ</span>
                        <?php else: ?>
                            <span class="badge badge-warning">ຍັງບໍສຳເລັດ</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['payment_date'] ? date('d/m/Y', strtotime($row['payment_date'])) : '-'; ?></td>
                    <td>
                        <a href="#" type="button" class="dashboard-edit-btn openEditOrderModal"
                            data-sid="<?php echo htmlspecialchars($row['s_id']); ?>"
                            data-cid="<?php echo htmlspecialchars($row['c_id']); ?>"
                            data-cname="<?php echo htmlspecialchars($row['c_name']); ?>"
                            data-pid="<?php echo htmlspecialchars($row['p_id']); ?>"
                            data-pname="<?php echo htmlspecialchars($row['p_name']); ?>"
                            data-qty="<?php echo htmlspecialchars($row['qty']); ?>"
                            data-price="<?php echo htmlspecialchars($row['price']); ?>"
                            data-status="<?php echo htmlspecialchars($row['order_status']); ?>"
                        >ແກ້ໄຂ</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeEditOrderModal">&times;</span>
            <h3 class="modal-title">ແກ້ໄຂຄຳສັ່ງຊື້</h3>
            <form id="editOrderForm" method="post">
                <input type="hidden" name="s_id" id="edit_s_id">
                <input type="hidden" name="c_id" id="edit_c_id">
                <input type="hidden" name="p_id" id="edit_p_id">
                <input type="hidden" name="price" id="edit_price">
                <input type="hidden" name="redirect" value="ordering_payment.php">
                <label>ລູກຄ້າ</label>
                <input type="text" name="c_name" id="edit_c_name" readonly style="width:100%;margin-bottom:10px;">
                <label>ສິນຄ້າ</label>
                <input type="text" name="p_name" id="edit_p_name" readonly style="width:100%;margin-bottom:10px;">
                <label>ຈຳນວນ</label>
                <input type="number" name="qty" id="edit_qty" required style="width:100%;margin-bottom:10px;">
                <label>ສະຖານະ</label>
                <select name="status" id="edit_status" style="width:100%;margin-bottom:10px;">
                    <option value="paid">ຈ່າຍເງິນແລ້ວ</option>
                    <option value="unpaid">ຍັງບໍ່ຈ່າຍ</option>
                </select>
                <button type="submit" class="dashboard-edit-btn" style="width:100%">ບັນທຶກ</button>
            </form>
        </div>
    </div>
    <style>
    .order-table-improved tr.even-row { background: #f9f9f9; }
    .order-table-improved tr.odd-row { background: #fff; }
    .order-table-improved tr:hover { background: #e3f2fd; }
    .badge { display: inline-block; padding: 2px 10px; border-radius: 12px; font-size: 0.95em; font-weight: 600; }
    .badge-success { background: #d4edda; color: #388e3c; }
    .badge-danger { background: #f8d7da; color: #e53935; }
    .badge-warning { background: #fff3cd; color: #b8860b; }
    </style>
    <script>
    // Edit Order Modal logic
    var orderModal = document.getElementById('editOrderModal');
    var closeOrderBtn = document.getElementById('closeEditOrderModal');
    var orderForm = document.getElementById('editOrderForm');
    Array.from(document.getElementsByClassName('openEditOrderModal')).forEach(function(btn) {
        btn.onclick = function(e) {
            e.preventDefault();
            document.getElementById('edit_s_id').value = btn.getAttribute('data-sid');
            document.getElementById('edit_c_id').value = btn.getAttribute('data-cid');
            document.getElementById('edit_c_name').value = btn.getAttribute('data-cname');
            document.getElementById('edit_p_id').value = btn.getAttribute('data-pid');
            document.getElementById('edit_p_name').value = btn.getAttribute('data-pname');
            document.getElementById('edit_qty').value = btn.getAttribute('data-qty');
            document.getElementById('edit_price').value = btn.getAttribute('data-price');
            document.getElementById('edit_status').value = btn.getAttribute('data-status');
            orderModal.style.display = 'block';
        };
    });
    closeOrderBtn.onclick = function() {
        orderModal.style.display = 'none';
    };
    window.onclick = function(event) {
        if (event.target == orderModal) {
            orderModal.style.display = 'none';
        }
    };
    orderForm.onsubmit = function(e) {
        e.preventDefault();
        var sid = document.getElementById('edit_s_id').value;
        orderForm.action = 'edit_order.php?id=' + encodeURIComponent(sid);
        orderForm.submit();
    };
    </script>
</body>
</html>