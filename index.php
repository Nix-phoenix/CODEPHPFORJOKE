<?php
include 'includes/auth.php';
include 'db/connection.php';

// Total sales (sum of all sales)
$total_sales = $conn->query("SELECT SUM(sd.total_price) as total FROM SellDetail sd")->fetch_assoc()['total'] ?? 0;

// Total orders (count from Sell table)
$total_orders = $conn->query("SELECT COUNT(*) as total FROM Sell")->fetch_assoc()['total'] ?? 0;

// Total products
$total_products = $conn->query("SELECT COUNT(*) as total FROM Product")->fetch_assoc()['total'] ?? 0;

// Total outstanding (sum of unpaid sales)
$total_outstanding = $conn->query("
    SELECT SUM(sd.total_price) as total
    FROM SellDetail sd
    JOIN Sell s ON sd.s_id = s.s_id
    WHERE s.status = 'unpaid'
")->fetch_assoc()['total'] ?? 0;

// Latest sales orders (limit 5)
$latest_sales = $conn->query("
    SELECT s.s_id, s.c_id, c.c_name, sd.p_id, p.p_name, sd.qty, sd.total_price, s.status
    FROM Sell s
    JOIN SellDetail sd ON s.s_id = sd.s_id
    JOIN Product p ON sd.p_id = p.p_id
    LEFT JOIN Customer c ON s.c_id = c.c_id
    ORDER BY s.date DESC
    LIMIT 5
");

// Low stock warning (products with qty_left <= 20)
$low_stock = $conn->query("
    SELECT p.p_name, 
        IFNULL(SUM(pod.qty), 0) - IFNULL((SELECT SUM(sd.qty) FROM SellDetail sd WHERE sd.p_id = p.p_id), 0) AS qty_left
    FROM Product p
    LEFT JOIN PurchaseOrderDetail pod ON p.p_id = pod.p_id
    GROUP BY p.p_id
    HAVING qty_left <= 20
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>ຫນ້າຫຼັກ - ລະບົບຈັດການຮ້ານGPG</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <h2 class="dashboard-title" style="margin-top:0;">ຫ້ອງຄວບຄຸມ</h2>
        <p class="dashboard-subtitle" style="margin-bottom:24px;">ຍິນດີຕ້ອນຮັບສູ່ລະບົບຈັດການຮ້ານຂອງທ່ານ!</p>
        <div class="dashboard-cards">
            <div class="dashboard-card">
                <div class="dashboard-card-title">ຍອດການຂາຍ</div>
                <div class="dashboard-card-value"><?php echo number_format($total_sales); ?>kip</div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-title">ຍອດການສັ່ງຊື້</div>
                <div class="dashboard-card-value"><?php echo $total_orders; ?> ຄຳສັ່ງ</div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-title">ຍອດສິນຄ້າ</div>
                <div class="dashboard-card-value"><?php echo $total_products; ?> ສິນຄ້າ</div>
            </div>
            <div class="dashboard-card">
                <div class="dashboard-card-title">ຍອດຄ້າງຈ່າຍ</div>
                <div class="dashboard-card-value"><?php echo number_format($total_outstanding); ?>kip</div>
            </div>
        </div>
        <h3 class="dashboard-section-title">ຄຳສັ່ງຂາຍຫຼ້າສຸດ</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ເລກທີສັ່ງຊື້</th>
                    <th>ລູກຄ້າ</th>
                    <th>ສິນຄ້າ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາ</th>
                    <th>ສະຖານະ</th>
                    <th>ແກ້ໄຂ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $latest_sales->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['s_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['c_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['qty']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_price']); ?>kip</td>
                    <td>
                        <?php
                        if ($row['status'] == 'paid') {
                            echo '<span style="color:#388e3c;">ຈ່າຍເງິນແລ້ວ</span>';
                        } else {
                            echo '<span style="color:#e53935;">ຄ້າງຈ່າຍ</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <button type="button" class="dashboard-edit-btn openEditOrderModal"
                            data-sid="<?php echo htmlspecialchars($row['s_id']); ?>"
                            data-cid="<?php echo htmlspecialchars($row['c_id']); ?>"
                            data-cname="<?php echo htmlspecialchars($row['c_name']); ?>"
                            data-pid="<?php echo htmlspecialchars($row['p_id']); ?>"
                            data-pname="<?php echo htmlspecialchars($row['p_name']); ?>"
                            data-qty="<?php echo htmlspecialchars($row['qty']); ?>"
                            data-price="<?php echo htmlspecialchars($row['total_price'] / max(1, $row['qty'])); ?>"
                            data-status="<?php echo htmlspecialchars($row['status']); ?>"
                        >ແກ້ໄຂ</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="dashboard-alert">
            <strong>ເຕືອນສິນຄ້າກຳລັງຂາດແຄ່ນ:</strong><br>
            <?php while($row = $low_stock->fetch_assoc()): ?>
                <?php echo htmlspecialchars($row['p_name']); ?>: <?php echo htmlspecialchars($row['qty_left']); ?> ສິນຄ້າ<br>
            <?php endwhile; ?>
        </div>
    </div>
    <!-- Edit Order Modal -->
    <div id="editOrderModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeEditOrderModal">&times;</span>
            <h3 class="modal-title">ແກ້ໄຂຄຳສັ່ງຂາຍ</h3>
            <form id="editOrderForm" method="post">
                <input type="hidden" name="s_id" id="edit_s_id">
                <input type="hidden" name="c_id" id="edit_c_id">
                <input type="hidden" name="p_id" id="edit_p_id">
                <input type="hidden" name="price" id="edit_price">
                <input type="hidden" name="redirect" value="index.php">
                <label>ລູກຄ້າ</label>
                <input type="text" name="c_name" id="edit_c_name" readonly style="width:100%;margin-bottom:10px;">
                <label>ສິນຄ້າ</label>
                <input type="text" name="p_name" id="edit_p_name" readonly style="width:100%;margin-bottom:10px;">
                <label>ຈຳນວນ</label>
                <input type="number" name="qty" id="edit_qty" required style="width:100%;margin-bottom:10px;">
                <label>ສະຖານະ</label>
                <select name="status" id="edit_status" style="width:100%;margin-bottom:10px;">
                    <option value="paid">ຈ່າຍເງິນແລ້ວ</option>
                    <option value="unpaid">ຄ້າງຈ່າຍ</option>
                </select>
                <button type="submit" class="dashboard-edit-btn" style="width:100%">ບັນທຶກ</button>
            </form>
        </div>
    </div>
    <script>
    // Edit Order Modal logic
    var orderModal = document.getElementById('editOrderModal');
    var closeOrderBtn = document.getElementById('closeEditOrderModal');
    var orderForm = document.getElementById('editOrderForm');
    Array.from(document.getElementsByClassName('openEditOrderModal')).forEach(function(btn) {
        btn.onclick = function() {
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