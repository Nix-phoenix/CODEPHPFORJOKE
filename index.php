<?php
include 'includes/auth.php';
include 'db/connection.php';

// Total sales (sum of all sales)
$total_sales = $conn->query("SELECT SUM(sd.total_price) as total FROM SellDetail sd")->fetch_assoc()['total'] ?? 0;

// Total orders
$total_orders = $conn->query("SELECT COUNT(*) as total FROM `Order`")->fetch_assoc()['total'] ?? 0;

// Total products
$total_products = $conn->query("SELECT COUNT(*) as total FROM Product")->fetch_assoc()['total'] ?? 0;

// Total outstanding (example: sum of unpaid sales, adjust as needed)
$total_outstanding = $conn->query("SELECT SUM(sd.total_price) as total FROM SellDetail sd")->fetch_assoc()['total'] ?? 0;

// Latest sales orders (limit 5)
$latest_sales = $conn->query("
    SELECT s.s_id, c.c_name, p.p_name, sd.qty, sd.total_price
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
        IFNULL(SUM(id.qty), 0) - IFNULL((SELECT SUM(sd.qty) FROM SellDetail sd WHERE sd.p_id = p.p_id), 0) AS qty_left
    FROM Product p
    LEFT JOIN ImportDetail id ON p.p_id = id.p_id
    GROUP BY p.p_id
    HAVING qty_left <= 20
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Homepage - Store Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-card { background: #2196f3; color: #fff; }
        .dashboard-card-value { font-size: 2rem; font-weight: bold; }
        .dashboard-card-title { font-size: 1.1rem; margin-bottom: 8px; }
        .dashboard-cards { display: flex; gap: 24px; margin: 24px 0; }
        .dashboard-card { flex: 1; padding: 24px 0; border-radius: 12px; text-align: center; }
        .dashboard-table th { background: #2196f3; color: #fff; }
        .dashboard-edit-btn { background: #4caf50; color: #fff; border: none; border-radius: 6px; padding: 6px 18px; font-size: 1rem; cursor: pointer; }
        .dashboard-alert { background: #ffd600; color: #222; padding: 18px 22px; border-radius: 8px; margin-top: 24px; font-size: 1.1rem; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <h2 class="dashboard-title">ຫ້ອງຄວບຄຸມ</h2>
        <p class="dashboard-subtitle">ຍິນດີຕ້ອນຮັບສູ່ລະບົບຂອງຮ້ານຂອງທ່ານ!</p>
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
                    <td><button class="dashboard-edit-btn">ແກ້ໄຂ</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="dashboard-alert">
            <strong>ເຕືອນສິນຄ້າທີ່ຈະຫມົດສົດ:</strong><br>
            <?php while($row = $low_stock->fetch_assoc()): ?>
                <?php echo htmlspecialchars($row['p_name']); ?>: <?php echo htmlspecialchars($row['qty_left']); ?> ສິນຄ້າ<br>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>