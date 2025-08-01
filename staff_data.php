<?php
include 'includes/auth.php';
include 'db/connection.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$message = '';

// Handle add / edit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid = intval($_POST['staffId'] ?? 0);
    $name = trim($_POST['staffName'] ?? '');
    $tel  = trim($_POST['staffPhone'] ?? '');
    $email = trim($_POST['staffEmail'] ?? '');
    $addr = trim($_POST['staffAddress'] ?? '');
    if ($name && $tel && $email && $addr) {
        if ($eid > 0) {
            $stmt = $conn->prepare('UPDATE Employee SET emp_name=?, tel=?, email=?, address=? WHERE emp_id=?');
            $stmt->bind_param('ssssi', $name, $tel, $email, $addr, $eid);
        } else {
            $stmt = $conn->prepare('INSERT INTO Employee (emp_name, tel, email, address) VALUES (?,?,?,?)');
            $stmt->bind_param('ssss', $name, $tel, $email, $addr);
        }
        if (!$stmt->execute()) {
            $message = 'DB Error: '.$stmt->error;
        }
        $stmt->close();
        header('Location: staff_data.php');
        exit;
    } else {
        $message = 'ກະລຸນາກຽບກອບຂໍ້ມູນໃຫ້ຄົບ';
    }
}

// Fetch staff list
$staffList = [];
$sql = "SELECT emp_id, emp_name, tel, email, address FROM Employee WHERE emp_name LIKE CONCAT('%',?,'%') OR tel LIKE CONCAT('%',?,'%') OR email LIKE CONCAT('%',?,'%') ORDER BY emp_id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sss', $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) { $staffList[] = $row; }
$stmt->close();
?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ບັນທືກຂໍ້ມູນພະນັກງານ - ລະບົບຈັດການຮ້ານGPG</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="dashboard-container">
        <h2 class="dashboard-title" style="margin-bottom: 18px;">ບັນທືກຂໍ້ມູນພະນັກງານ</h2>
        <!-- Search Bar -->
        <form method="get" style="display:flex;gap:12px;margin-bottom:18px;">
            <input type="text" name="search" placeholder="ຄົ້ນຫາ (ພະນັກງານ, ເບີໂທ, ອີເມລ)" value="<?php echo htmlspecialchars($search); ?>" style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="dashboard-add-btn" style="width:180px;">ຄົ້ນຫາ</button>
        </form>
        <a href="staff_add.php" class="dashboard-add-btn" onclick="window.open('staff_add.php','addstaff','width=600,height=620'); return false;" style="margin-bottom:18px;width:200px;display:inline-block;text-align:center;">ເພີ່ມຂໍ້ມູນພະນັກງານ</a>

        <!-- Staff Data Table -->
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ລະຫັດ</th>
                    <th>ຊື່ ແລະ ນາມສະກຸນ</th>
                    <th>ເບີໂທ</th>
                    <th>ອີເມລ</th>
                    <th>ທີ່ຢູ່</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody id="staffTableBody">
<?php if (empty($staffList)): ?>
    <tr><td colspan="6" style="text-align:center;">ບໍ່ມີຂໍ້ມູນ</td></tr>
<?php else: ?>
<?php foreach ($staffList as $index => $st): ?>
    <tr>
        <td><?php echo $index + 1; ?></td>
        <td><?php echo htmlspecialchars($st['emp_name']); ?></td>
        <td><?php echo htmlspecialchars($st['tel']); ?></td>
        <td><?php echo htmlspecialchars($st['email']); ?></td>
        <td><?php echo htmlspecialchars($st['address']); ?></td>
        <td>
            <a class="dashboard-edit-btn" href="staff_edit.php?id=<?php echo $st['emp_id']; ?>" onclick="window.open(this.href,'editstaff','width=600,height=620'); return false;">ແກ້ໄຂ</a>
            <a class="dashboard-delete-btn" href="staff_delete.php?id=<?php echo $st['emp_id']; ?>" onclick="return confirm('ຢືນຢັນການລົບ?');">ລົບ</a>
        </td>
    </tr>
<?php endforeach; ?>
<?php endif; ?>
</tbody>
        </table>
    </div>
</div>
</script>
</html>
