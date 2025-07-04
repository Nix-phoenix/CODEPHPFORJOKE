<?php
include 'includes/auth.php';
include 'db/connection.php';
$sql = "SELECT p.p_id, p.p_name, p.price, pt.pt_name, pb.pb_name, pslf.pslf_location, punit.punit_name 
        FROM Product p 
        LEFT JOIN ProductType pt ON p.pt_id = pt.pt_id
        LEFT JOIN ProductBrand pb ON p.pb_id = pb.pb_id
        LEFT JOIN ProductShelf pslf ON p.pslf_id = pslf.pslf_id
        LEFT JOIN ProductUnit punit ON p.punit_id = punit.punit_id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Storage of Goods - Store Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <h2 class="dashboard-title">ຈັດການສິນຄ້າ</h2>
        <button class="dashboard-add-btn" id="openAddProductModal">ເພີ່ມສິນຄ້າໃໝ່</button>

        <div id="addProductModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeAddProductModal">&times;</span>
                <h3 class="modal-title">ເພີ່ມສິນຄ້າໃໝ່</h3>
                <form action="add_product.php" method="post">
                    <label>ລະຫັດສິນຄ້າ</label>
                    <input type="text" name="p_id" required>
                    <label>ຊື່ສິນຄ້າ</label>
                    <input type="text" name="p_name" required>
                    <label>ລາຄາ</label>
                    <input type="number" name="price" required>
                    <label>ຈຳນວນ</label>
                    <input type="number" name="quantity" required>
                    <label>ຫົວໜ່ວຍ</label>
                    <input type="text" name="punit_name" required>
                    <label>ຊັ້ນວາງ</label>
                    <input type="text" name="pslf_location" required>
                    <label>ປະເພດ</label>
                    <input type="text" name="pt_name" required>
                    <button type="submit" class="dashboard-edit-btn" style="width:100%;">ບັນທຶກ</button>
                </form>
            </div>
        </div>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ລະຫັດສິນຄ້າ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ລາຄາ</th>
                    <th>ຈຳນວນ</th>
                    <th>ຫົວໜ່ວຍ</th>
                    <th>ຊັ້ນວາງ</th>
                    <th>ປະເພດ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['p_id']; ?></td>
                    <td><?php echo $row['p_name']; ?></td>
                    <td><?php echo $row['price']; ?>kip</td>
                    <td>100</td>
                    <td><?php echo $row['punit_name']; ?></td>
                    <td><?php echo $row['pslf_location']; ?></td>
                    <td><?php echo $row['pt_name']; ?></td>
                    <td>
                        <button class="dashboard-edit-btn">ແກ້ໄຂ</button>
                        <button class="dashboard-delete-btn">ລົບ</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>