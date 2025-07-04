<?php
include 'includes/auth.php';
include 'db/connection.php';

// Fetch products
$product_sql = "SELECT p.p_id, p.p_name, p.price, pslf.pslf_location, punit.punit_name
                FROM Product p
                LEFT JOIN ProductShelf pslf ON p.pslf_id = pslf.pslf_id
                LEFT JOIN ProductUnit punit ON p.punit_id = punit.punit_id";
$product_result = $conn->query($product_sql);

// Fetch import records
$import_sql = "SELECT 
    ip.ip_id,
    ip.date AS import_date,
    p.p_name,
    s.sup_name,
    s.tel,
    s.address,
    ipd.qty AS import_qty
FROM Import ip
LEFT JOIN ImportDetail ipd ON ip.ip_id = ipd.ip_id
LEFT JOIN Product p ON ipd.p_id = p.p_id
LEFT JOIN `Order` o ON ip.od_id = o.od_id
LEFT JOIN Supplier s ON o.sup_id = s.sup_id";
$import_result = $conn->query($import_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Warehouse & Import - Store Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <h2 class="dashboard-title">ຄັງສິນຄ້າ & ການນຳເຂົ້າ</h2>
        <form style="display:flex;gap:16px;margin-bottom:18px;">
            <input type="text" placeholder="ຄົ້ນຫາສິນຄ້າ..." style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="dashboard-add-btn" style="width:200px;">ຄົ້ນຫາ</button>
        </form>

        <h3 class="dashboard-section-title" style="margin-bottom:8px;">ສິນຄ້າຄັງ</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ລະຫັດສິນຄ້າ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ລາຄາ</th>
                    <th>ຫົວໜ່ວຍ</th>
                    <th>ຊັ້ນວາງ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $product_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['p_id']; ?></td>
                    <td><?php echo $row['p_name']; ?></td>
                    <td><?php echo $row['price']; ?></td>
                    <td><?php echo $row['punit_name']; ?></td>
                    <td><?php echo $row['pslf_location']; ?></td>
                    <td><button class="dashboard-edit-btn">ແກ້ໄຂ</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 class="dashboard-section-title" style="margin:18px 0 8px 0;">ການນຳເຂົ້າສິນຄ້າເຂົ້າ</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ຜູ້ຈັດຫາ</th>
                    <th>ເບີໂທ</th>
                    <th>ທີ່ຢູ່</th>
                    <th>ຈຳນວນນຳເຂົ້າ</th>
                    <th>ວັນທີນຳເຂົ້າ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $import_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['p_name']; ?></td>
                    <td><?php echo $row['sup_name']; ?></td>
                    <td><?php echo $row['tel']; ?></td>
                    <td><?php echo $row['address']; ?></td>
                    <td><?php echo $row['import_qty']; ?></td>
                    <td><?php echo $row['import_date']; ?></td>
                    <td><button class="dashboard-edit-btn">ແກ້ໄຂ</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>