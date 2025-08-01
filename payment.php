<?php
require_once 'includes/auth.php';
require_once 'db/connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$like_search = "%$search%";

// Fetch sales data join with payment to get the status.
$sql = "SELECT 
            s.s_id,
            c.c_name,
            p.p_name,
            p.type as p_type,
            sd.qty,
            sd.price,
            sd.total_price,
            pm.pm_id,
            pm.status AS payment_status
        FROM Sell s
        JOIN SellDetail sd ON s.s_id = sd.s_id
        JOIN Product p ON sd.p_id = p.p_id
        LEFT JOIN Customer c ON s.c_id = c.c_id
        LEFT JOIN Payment pm ON s.s_id = pm.s_id";

if (!empty($search)) {
    $sql .= " WHERE s.s_id LIKE ? OR c.c_name LIKE ? OR p.p_name LIKE ?";
}
$sql .= " GROUP BY sd.sd_id ORDER BY s.s_id DESC";

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $stmt->bind_param('sss', $like_search, $like_search, $like_search);
}
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function laoNumberFormat($value) {
    return number_format((float)$value, 0, '.', ',') . ' ກິບ';
}
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຈັດການການຊຳລະ</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background-color: #f0f2f5; font-family: 'Noto Sans Lao', sans-serif; }
        .container-box { max-width: 1200px; margin: 20px auto; padding: 24px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .page-header { font-size: 22px; font-weight: 700; margin-bottom: 16px; }
        .search-form { display: flex; gap: 10px; margin-bottom: 16px; }
        .search-input { flex-grow: 1; padding: 8px 12px; border: 1px solid #ccc; border-radius: 6px; }
        .btn { padding: 8px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; color: white; }
        .btn-green { background-color: #28a745; }
        .btn-red { background-color: #dc3545; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th { background-color: #007bff; color: white; padding: 12px; text-align: center; font-weight: bold; }
        .data-table tbody td { padding: 10px; border-bottom: 1px solid #e0e0e0; text-align: center; }
        .data-table tbody tr:nth-child(even) { background-color: #f8f9fa; }
        .data-table td.text-left { text-align: left; }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="container-box">
        <h2 class="page-header">ຈັດການການຊຳລະ</h2>
        
        <form method="get" class="search-form">
            <input type="text" name="search" class="search-input" placeholder="ຄົ້ນຫາ (ເລກທີບິນ, ລູກຄ້າ, ສິນຄ້າ)" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-green">ຄົ້ນຫາ</button>
        </form>

        <a href="sale.php" class="btn btn-green" style="margin-bottom: 16px;">ເພີ່ມການຊຳລະໃໝ່</a>

        <table class="data-table">
            <thead>
                <tr>
                    <th>ເລກທີຊຳລະ</th>
                    <th>ຊື່ລູກຄ້າ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ປະເພດ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th>ລາຄາລວມ</th>
                    <th>ສະຖານະ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['s_id']); ?></td>
                            <td class="text-left"><?php echo htmlspecialchars($row['c_name'] ?? 'Walk-in'); ?></td>
                            <td class="text-left"><?php echo htmlspecialchars($row['p_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['p_type']); ?></td>
                            <td><?php echo htmlspecialchars($row['qty']); ?></td>
                            <td><?php echo laoNumberFormat($row['price']); ?></td>
                            <td><?php echo laoNumberFormat($row['total_price']); ?></td>
                            <td>
                                <span style="font-weight:bold; color:<?php echo $row['payment_status'] === 'paid' ? 'green' : 'red'; ?>">
                                    <?php echo $row['payment_status'] === 'paid' ? 'ຈ່າຍແລ້ວ' : 'ຄ້າງຊຳລະ'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="payment_api.php?action=toggle_status&sid=<?php echo $row['s_id']; ?>" class="btn btn-green">ແກ້ໄຂ</a>
                                <a href="payment_api.php?action=delete_sale&sid=<?php echo $row['s_id']; ?>" onclick="return confirm('ການລົບລາຍການຂາຍນີ້ຈະລົບການຈ່າຍເງິນທີ່ກ່ຽວຂ້ອງນຳ. ດຳເນີນການຕໍ່?')" class="btn btn-red">ລົບ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" style="padding: 20px;">ບໍ່ມີຂໍ້ມູນ</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
