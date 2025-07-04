<?php
include 'includes/auth.php';
include 'db/connection.php';
// Add order and payment logic here
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
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ເລກທີຄຳສັ່ງ</th>
                    <th>ຊື່ລູກຄ້າ</th>
                    <th>ສິນຄ້າ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາລວມ</th>
                    <th>ສະຖານະ</th>
                    <th>ສະຖານະການຈ່າຍ</th>
                    <th>ວິທີການຈ່າຍ</th>
                    <th>ວັນທີຈ່າຍ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1001</td>
                    <td>ທ້າວ ຈອນ</td>
                    <td>ທ່ອນນ້ຳ A</td>
                    <td>10</td>
                    <td>500kip</td>
                    <td>ຈ່າຍເງິນແລ້ວ</td>
                    <td>ສຳເລັດ</td>
                    <td>ບັດເຄຣດິດ</td>
                    <td>2025-03-20</td>
                    <td><button class="dashboard-edit-btn">ແກ້ໄຂ</button></td>
                </tr>
                <tr>
                    <td>1002</td>
                    <td>ທ້າວ ເມຣີ</td>
                    <td>ທ່ອນນ້ຳ B</td>
                    <td>5</td>
                    <td>150kip</td>
                    <td>ຍັງບໍ່ຈ່າຍ</td>
                    <td>ຮອຍສຳເລັດ</td>
                    <td>ຊຳລະດ້ວຍເງິນສົດ</td>
                    <td>ຍັງບໍ່ຈ່າຍ</td>
                    <td><button class="dashboard-edit-btn">ແກ້ໄຂ</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>