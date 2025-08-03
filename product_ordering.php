<?php
include 'includes/auth.php';
include 'db/connection.php';

// ຮັບຄ່າການຄົ້ນຫາຈາກ URL
$search = isset($_GET['search']) ? $_GET['search'] : '';

// ເອົາຂໍ້ມູນສຳລັບ dropdowns
$suppliers = $conn->query("SELECT sup_id, sup_name FROM Supplier ORDER BY sup_name ASC")->fetch_all(MYSQLI_ASSOC);
$employees = $conn->query("SELECT emp_id, emp_name FROM Employee ORDER BY emp_name ASC")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT p_id, p_name, price FROM Product ORDER BY p_name ASC")->fetch_all(MYSQLI_ASSOC);

// ເອົາລາຍການສັ່ງຊື້ ພ້ອມກັບການ join ງ່າຍໆ (ໜຶ່ງສິນຄ້າຕໍ່ໜຶ່ງຄຳສັ່ງຊື້)
$stmt = $conn->prepare("SELECT po.po_id AS id, po.sup_id, po.emp_id, p.p_id, sup.sup_name AS supplier, emp.emp_name AS employee, p.p_name AS product, pod.qty, po.date, pod.price AS unitPrice FROM PurchaseOrder po JOIN Supplier sup ON po.sup_id=sup.sup_id JOIN Employee emp ON po.emp_id=emp.emp_id JOIN PurchaseOrderDetail pod ON pod.po_id=po.po_id JOIN Product p ON p.p_id=pod.p_id WHERE sup.sup_name LIKE ? OR p.p_name LIKE ? OR po.po_id LIKE ? ORDER BY po.po_id DESC");
$like = '%' . $search . '%';
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຈັດການການສັ່ງຊື້ສິນຄ້າ - ລະບົບຈັດການຮ້ານGPG</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --primary-blue: #1b81e7ff;
            --primary-green: #38b449;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #26e63cff;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #dee2e6;
            --bg-color: #ffffff;
            --font-family-lao: 'Noto Sans Lao', sans-serif;
        }

        body {
            font-family: var(--font-family-lao);
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
        }

        .navbar {
            background-color: green;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 700;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .dashboard-container {
            background-color: var(--bg-color);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e0e6ed;
        }

        .dashboard-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 24px;
        }

        .action-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 24px;
        }

        .action-buttons-left {
            display: flex;
            gap: 10px;
        }

        .search-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-input {
            width: 250px;
            /* Adjust width as needed */
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 1rem;
        }

        .dashboard-btn {
            font-size: 1rem;
            font-weight: 700;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            white-space: nowrap;
        }

        .btn-green {
            background-color: var(--primary-green);
        }

        .btn-green:hover {
            background-color: #2e913a;
        }

        .btn-blue {
            background-color: var(--primary-blue);
        }

        .btn-blue:hover {
            background-color: #1a74d2;
        }

        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .dashboard-table th,
        .dashboard-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .dashboard-table thead th {
            background-color: var(--primary-blue);
            color: var(--light-color);
            font-weight: 700;
        }

        .dashboard-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .dashboard-table tbody tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.3s ease;
        }

        .dashboard-edit-btn,
        .dashboard-delete-btn {
            font-size: 0.9rem;
            font-weight: 700;
            padding: 8px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            color: white;
            width: 60px;
            /* Fixed width for better alignment */
        }

        .dashboard-edit-btn {
            background-color: var(--warning-color);
        }

        .dashboard-delete-btn {
            background-color: var(--danger-color);
        }

        .action-btn-group {
            display: flex;
            gap: 5px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            padding-top: 50px;
            overflow: auto;
        }

        .modal-content {
            background-color: var(--bg-color);
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 15px;
            right: 20px;
            cursor: pointer;
        }

        .modal-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--dark-color);
        }

        .gpg-input {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box;
            font-family: var(--font-family-lao);
        }

        .gpg-input:focus {
            outline: none;
            border-color: var(--primary-blue);
        }

        .btn-group {
            text-align: center;
            margin-top: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .action-container {
                flex-direction: column;
                align-items: stretch;
            }

            .search-form {
                flex-direction: column;
            }
        }
    </style>
</head>

<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="dashboard-container">
        <h2 class="dashboard-title">ຈັດການການສັ່ງຊື້ສິນຄ້າ</h2>

<div class="action-container">
    <!-- Right side search bar -->
    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="ຄົ້ນຫາ (ຜູ້ສະຫນອງ, ສິນຄ້າ, ເລກທີ)" value="<?php echo htmlspecialchars($search); ?>" class="search-input" style="width: 700px; max-width: 100%; height: 40px;">
        <button type="submit" class="dashboard-btn btn-green" style="width: 120px; height: 60px;">ຄົ້ນຫາ</button>
    </form>
</div>

        <div class="action-buttons-left">
            <button type="button" class="dashboard-btn btn-green" onclick="openAddModal()">ເພີ່ມການສັ່ງຊື້ໃໝ່</button>
            <a href="order_report.php" class="dashboard-btn btn-green">ພິມລາຍງານ</a>
        </div>

        <!-- Orders Table -->
        <div style="overflow-x:auto;">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ລະຫັດຄຳສັ່ງຊື້</th>
                        <th>ຜູ້ສະຫນອງ</th>
                        <th>ພະນັກງານຮັບອອກຄຳສັ່ງ</th>
                        <th>ສິນຄ້າ</th>
                        <th>ຈຳນວນ</th>
                        <th>ວັນທີສັ່ງ</th>
                        <th>ລາຄາຕໍ່ຫນ່ວຍ</th>
                        <th>ລາຄາລວມ</th>
                        <th>ຈຳນວນເງິນທີ່ຈ່າຍ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>
                <tbody id="orderTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Order Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 class="modal-title" id="modalTitle">ເພີ່ມການສັ່ງຊື້ໃໝ່</h3>
        <form id="orderForm">
            <input type="hidden" name="orderId" id="orderId">
            <label for="supplierId">ຜູ້ສະຫນອງ:</label>
            <select id="supplierId" name="supplierId" class="gpg-input" required>
                <?php foreach ($suppliers as $s) {
                    echo "<option value='{$s['sup_id']}'>{$s['sup_name']}</option>";
                } ?>
            </select>
            <label for="employeeId">ພະນັກງານຮັບອອກຄຳສັ່ງ:</label>
            <select id="employeeId" name="employeeId" class="gpg-input" required>
                <?php foreach ($employees as $e) {
                    echo "<option value='{$e['emp_id']}'>{$e['emp_name']}</option>";
                } ?>
            </select>
            <label for="productId">ສິນຄ້າ:</label>
            <select id="productId" name="productId" class="gpg-input" required onchange="updateUnitPrice()">
                <?php foreach ($products as $p) {
                    echo "<option value='{$p['p_id']}' data-price='{$p['price']}'>{$p['p_name']}</option>";
                } ?>
            </select>
            <label for="orderQty">ຈຳນວນ:</label>
            <input type="number" id="orderQty" name="orderQty" class="gpg-input" required>
            <label for="orderDate">ວັນທີສັ່ງ:</label>
            <input type="date" id="orderDate" name="orderDate" class="gpg-input" required>
            <label for="unitPrice">ລາຄາຕໍ່ຫນ່ວຍ:</label>
            <input type="number" id="unitPrice" name="unitPrice" class="gpg-input" required>
            <div class="btn-group">
                <button type="submit" class="dashboard-btn btn-green">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<script>
    // ຂໍ້ມູນ PHP ທີ່ຖືກແປງເປັນ JavaScript
    const suppliers = <?php echo json_encode($suppliers, JSON_UNESCAPED_UNICODE); ?>;
    const employees = <?php echo json_encode($employees, JSON_UNESCAPED_UNICODE); ?>;
    const products = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE); ?>;
    let orders = <?php echo json_encode($orders, JSON_UNESCAPED_UNICODE); ?>;

    // ຄິດໄລ່ລາຄາລວມ
    function calcTotal(o) {
        return o.qty * o.unitPrice;
    }

    // ອັບເດດລາຄາຕໍ່ໜ່ວຍເມື່ອເລືອກສິນຄ້າ
    function updateUnitPrice() {
        const sel = document.getElementById('productId');
        const price = parseFloat(sel.options[sel.selectedIndex].dataset.price || 0);
        document.getElementById('unitPrice').value = price;
    }

    // ເປີດ modal ສໍາລັບເພີ່ມການສັ່ງຊື້ໃໝ່
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'ເພີ່ມການສັ່ງຊື້ໃໝ່';
        document.getElementById('orderForm').reset();
        document.getElementById('orderId').value = '';
        document.getElementById('orderModal').style.display = 'block';
    }

    // ເປີດ modal ສໍາລັບແກ້ໄຂການສັ່ງຊື້
    function editOrder(id) {
        const o = orders.find(x => x.id === id);
        if (!o) return;
        document.getElementById('modalTitle').textContent = 'ແກ້ໄຂການສັ່ງຊື້';
        document.getElementById('orderId').value = o.id;
        document.getElementById('supplierId').value = o.sup_id;
        document.getElementById('employeeId').value = o.emp_id;
        document.getElementById('productId').value = o.p_id;
        document.getElementById('orderQty').value = o.qty;
        document.getElementById('orderDate').value = o.date;
        document.getElementById('unitPrice').value = o.unitPrice;
        document.getElementById('orderModal').style.display = 'block';
    }

    // ລົບການສັ່ງຊື້
    function deleteOrder(id) {
        if (confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບ?')) {
            fetch('purchase_order_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'delete',
                        id
                    })
                })
                .then(r => r.json()).then(res => {
                    if (res.success) {
                        orders = res.orders;
                        renderTable();
                        alert('ລົບການສັ່ງຊື້ສຳເລັດແລ້ວ.');
                    } else alert(res.error || 'ເກີດຂໍ້ຜິດພາດ.');
                })
                .catch(err => alert('ເກີດຂໍ້ຜິດພາດໃນການລົບ: ' + err.message));
        }
    }

    // ປິດ modal
    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    // ສະແດງຕາຕະລາງຂໍ້ມູນ
    function renderTable() {
        const tbody = document.getElementById('orderTableBody');
        tbody.innerHTML = '';
        orders.forEach((o, i) => {
            const total = calcTotal(o);
            const newRow = tbody.insertRow();
            newRow.innerHTML = `
                <td>${o.id}</td>
                <td>${o.supplier}</td>
                <td>${o.employee}</td>
                <td>${o.product}</td>
                <td>${o.qty}</td>
                <td>${new Date(o.date).toLocaleDateString('en-GB')}</td>
                <td>${parseFloat(o.unitPrice).toLocaleString()} ກີບ</td>
                <td>${total.toLocaleString()} ກີບ</td>
                <td>${total.toLocaleString()} ກີບ</td>
                <td>
                    <div class="action-btn-group">
                        <button class="dashboard-edit-btn" onclick="editOrder(${o.id})">ແກ້ໄຂ</button>
                        <button class="dashboard-delete-btn" onclick="deleteOrder(${o.id})">ລົບ</button>
                    </div>
                </td>
            `;
        });
    }

    // ຈັດການການສົ່ງແບບຟອມ (ເພີ່ມ/ແກ້ໄຂ)
    document.getElementById('orderForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this); // Use FormData
        const id = formData.get('orderId');

        formData.append('action', id ? 'edit' : 'add'); // Add action

        fetch('purchase_order_api.php', { // Corrected URL
                method: 'POST',
                body: formData // Send FormData
            })
            .then(response => response.json()) // Parse the JSON response
            .then(data => {
                if (data.success) {
                    orders = data.orders;
                    renderTable();
                    closeModal();
                    alert('ບັນທຶກຂໍ້ມູນສຳເລັດແລ້ວ.');
                } else {
                    alert(data.error || 'ເກີດຂໍ້ຜິດພາດ.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('ເກີດຂໍ້ຜິດພາດໃນການບັນທຶກ: ' + error.message);
            });
    });

    // ປິດ modal ເມື່ອກົດນອກພື້ນທີ່ modal
    window.onclick = e => {
        if (e.target === document.getElementById('orderModal')) closeModal();
    };

    // ເມື່ອໜ້າເວັບໂຫຼດສຳເລັດ ໃຫ້ສະແດງຕາຕະລາງ
    document.addEventListener('DOMContentLoaded', renderTable);
</script>

</html>