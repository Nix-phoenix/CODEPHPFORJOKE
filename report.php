<?php
include 'includes/auth.php';
include 'db/connection.php';

// Sales report: total sales per day and best-selling product
$sales_sql = "
    SELECT 
        DATE(s.date) as sale_date,
        SUM(sd.total_price) as total_sales,
        (SELECT p.p_name 
         FROM SellDetail sd2 
         JOIN Product p ON sd2.p_id = p.p_id 
         WHERE sd2.s_id = s.s_id 
         ORDER BY sd2.qty DESC LIMIT 1) as best_product
    FROM Sell s
    JOIN SellDetail sd ON s.s_id = sd.s_id
    GROUP BY DATE(s.date)
    ORDER BY sale_date DESC
    LIMIT 10
";
$sales_result = $conn->query($sales_sql);

// Income & expense report (example: you may need to adjust for your schema)
$income_expense_sql = "
    SELECT 
        DATE(s.date) as sale_date,
        SUM(sd.total_price) as income,
        IFNULL((
            SELECT SUM(id.price * id.qty)
            FROM ImportDetail id
            JOIN Import i ON id.ip_id = i.ip_id
            WHERE DATE(i.date) = DATE(s.date)
        ), 0) as expense,
        SUM(sd.total_price) - IFNULL((
            SELECT SUM(id.price * id.qty)
            FROM ImportDetail id
            JOIN Import i ON id.ip_id = i.ip_id
            WHERE DATE(i.date) = DATE(s.date)
        ), 0) as profit
    FROM Sell s
    JOIN SellDetail sd ON s.s_id = sd.s_id
    GROUP BY DATE(s.date)
    ORDER BY sale_date DESC
    LIMIT 10
";
$income_expense_result = $conn->query($income_expense_sql);

// Inventory report: product and quantity left
$inventory_sql = "
    SELECT 
        p.p_name,
        IFNULL(SUM(id.qty), 0) - IFNULL((
            SELECT SUM(sd.qty) FROM SellDetail sd WHERE sd.p_id = p.p_id
        ), 0) AS qty_left
    FROM Product p
    LEFT JOIN ImportDetail id ON p.p_id = id.p_id
    GROUP BY p.p_id
    ORDER BY qty_left DESC
    LIMIT 10
";
$inventory_result = $conn->query($inventory_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>ລາຍງານ - Store Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <h2 class="dashboard-title">ລາຍງານລາຍວັນ</h2>
        <form style="display:flex;gap:16px;margin-bottom:18px;">
            <input type="text" placeholder="ຄົ້ນຫາລາຍງານ...." style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="dashboard-add-btn" style="width:200px;">ຄົ້ນຫາ</button>
        </form>

        <h3 style="margin-bottom:8px;">ລາຍງານການຂາຍ</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ວັນທີ</th>
                    <th>ຍອດຂາຍລວມ</th>
                    <th>ສິນຄ້າຂາຍດີ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $sales_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_sales']); ?></td>
                    <td><?php echo htmlspecialchars($row['best_product']); ?></td>
                    <td><button class="dashboard-edit-btn">View</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 style="margin:18px 0 8px 0;">ລາຍງານຮັບເງິນ & ຈ່າຍເງິນ</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ວັນທີ</th>
                    <th>ຮັບເງິນ</th>
                    <th>ຈ່າຍເງິນ</th>
                    <th>ກຳໄລ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $income_expense_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['income']); ?>kip</td>
                    <td><?php echo htmlspecialchars($row['expense']); ?>kip</td>
                    <td><?php echo htmlspecialchars($row['profit']); ?>kip</td>
                    <td><button class="dashboard-edit-btn">View</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3 style="margin:18px 0 8px 0;">ລາຍງານຄົງສິນຄ້າ</h3>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ຊື່ສິນຄໍາ</th>
                    <th>ຈຳນວນຄົງເຫຼືອ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $inventory_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['qty_left']); ?></td>
                    <td><button class="dashboard-edit-btn">View</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="assets/js/scripts.js"></script>
</body>
</html>