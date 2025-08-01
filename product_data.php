<?php
include 'includes/auth.php';
include 'db/connection.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch products from database based on search
$stmt = $conn->prepare("SELECT p_id AS id, p_name AS name, category, type, shelf, unit, qty, price, image_path FROM Product WHERE p_name LIKE ? OR type LIKE ? ORDER BY p_id ASC");
$like = '%' . $search . '%';
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ບັນທຶກຂໍ້ມູນສິນຄ້າ - ລະບົບຈັດການຮ້ານGPG</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* General Body and Container Styles */
        body {
            background-color: #f4f7f9;
            font-family: 'Noto Sans Lao', sans-serif;
            line-height: 1.6;
            color: #444;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        /* Dashboard Container and Title */
        .dashboard-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        /* Search and Add Button Group */
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 15px;
        }

        .search-form {
            display: flex;
            flex: 1;
            gap: 10px;
        }

        .search-input {
            flex: 1;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 6px;
            border: none;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: #007bff;
        }
        
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        .btn-add {
            background-color: #28a745;
            width: 200px;
        }
        
        .btn-add:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        /* Data Table */
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .dashboard-table thead tr {
            background-color: #f0f4f7;
            color: #555;
            text-align: left;
        }

        .dashboard-table th, .dashboard-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 15px;
            text-align: left;
        }

        .dashboard-table th:first-child, .dashboard-table td:first-child {
            text-align: center;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: none;
        }

        .dashboard-table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border-radius: 4px;
            border: none;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }

        .btn-edit {
            background-color: #23af46ff;
        }

        .btn-edit:hover {
            background-color: #e0a800;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.3s;
        }

        .close:hover, .close:focus {
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .gpg-input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .gpg-input:focus {
            border-color: #007bff;
            outline: none;
        }

        .gpg-input[type="file"] {
            padding: 10px;
        }

        label {
            display: block;
            font-weight: 700;
            margin-bottom: 8px;
            color: #555;
        }

        .modal-buttons {
            text-align: center;
            margin-top: 20px;
        }
        
        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            .btn-add {
                width: 100%;
            }
            .dashboard-table {
                border-radius: 8px;
            }
            .dashboard-table thead {
                display: none; /* Hide header on mobile */
            }
            .dashboard-table tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                background-color: #fff;
                padding: 15px;
            }
            .dashboard-table td {
                display: block;
                border-bottom: 1px solid #e0e0e0;
                position: relative;
                padding-left: 50%;
                text-align: right;
                font-size: 14px;
            }
            .dashboard-table td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: bold;
                color: #555;
            }
            .dashboard-table td:last-child {
                border-bottom: none;
            }
            .action-buttons {
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="dashboard-container">
        <h2 class="dashboard-title">ບັນທຶກຂໍ້ມູນສິນຄ້າ</h2>

        <div class="action-bar">
            <form method="get" class="search-form">
                <input type="text" name="search" placeholder="ຄົ້ນຫາ (ສິນຄ້າ, ໝວດ, ຍິ່ຫໍ້)" value="<?php echo htmlspecialchars($search); ?>" class="search-input">
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> ຄົ້ນຫາ</button>
            </form>
            <button type="button" class="btn btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i> ເພີ່ມຂໍ້ມູນສິນຄ້າ
            </button>
        </div>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="width: 5%;">ລຳດັບ</th>
                    <th style="width: 10%;">ຮູບພາບ</th>
                    <th style="width: 20%;">ຊື່ສິນຄ້າ</th>
                    <th style="width: 15%;">ປະເພດສິນຄ້າ</th>
                    <th style="width: 15%;">ໝວດສິນຄ້າ</th>
                    <th style="width: 10%;">ຖານວາງ</th>
                    <th style="width: 10%;">ຈຳນວນ</th>
                    <th style="width: 15%;">ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th style="width: 15%; text-align: center;">ຈັດການ</th>
                </tr>
            </thead>
            <tbody id="productTableBody"></tbody>
        </table>
    </div>
</div>

<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 class="modal-title" id="modalTitle">ເພີ່ມຂໍ້ມູນສິນຄ້າ</h3>
        <form id="productForm" enctype="multipart/form-data">
            <input type="hidden" id="productId" name="productId">
            <label for="productImage">ຮູບພາບ:</label>
            <input type="file" id="productImage" name="productImage" class="gpg-input">
            <label for="productName">ຊື່ສິນຄ້າ:</label>
            <input type="text" id="productName" name="productName" class="gpg-input" required>
            <label for="productCategory">ໝວດສິນຄ້າ:</label>
            <input type="text" id="productCategory" name="productCategory" class="gpg-input" required>
            <label for="productType">ປະເພດສິນຄ້າ:</label>
            <input type="text" id="productType" name="productType" class="gpg-input" required>
            <label for="productShelf">ຖານວາງ:</label>
            <input type="text" id="productShelf" name="productShelf" class="gpg-input" required>
            <label for="productUnit">ຫນ່ວຍ:</label>
            <input type="text" id="productUnit" name="productUnit" class="gpg-input" required>
            <label for="productQty">ຈຳນວນ:</label>
            <input type="number" id="productQty" name="productQty" class="gpg-input" required>
            <label for="productPrice">ລາຄາຕໍ່ໜ່ວຍ:</label>
            <input type="number" id="productPrice" name="productPrice" class="gpg-input" required>
            <div class="modal-buttons">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<script>
    let products = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE); ?>;

    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'ເພີ່ມຂໍ້ມູນສິນຄ້າ';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value = '';
        document.getElementById('productModal').style.display = 'block';
    }

    function editProduct(id) {
        const p = products.find(x => x.id === id);
        if (!p) return;
        document.getElementById('modalTitle').textContent = 'ແກ້ໄຂຂໍ້ມູນສິນຄ້າ';
        document.getElementById('productId').value = p.id;
        document.getElementById('productName').value = p.name;
        document.getElementById('productCategory').value = p.category;
        document.getElementById('productType').value = p.type;
        document.getElementById('productShelf').value = p.shelf;
        document.getElementById('productUnit').value = p.unit;
        document.getElementById('productQty').value = p.qty;
        document.getElementById('productPrice').value = p.price;
        // Note: Cannot set value for file input, user must re-select
        document.getElementById('productModal').style.display = 'block';
    }

    function deleteProduct(id) {
        if (!confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບ?')) return;
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('product_api.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    products = res.products;
                    renderTable();
                    alert('ລົບສຳເລັດແລ້ວ');
                } else {
                    alert(res.error || 'Error');
                }
            })
            .catch(() => alert('Network error'));
    }

    function closeModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    function renderTable() {
        const tbody = document.getElementById('productTableBody');
        tbody.innerHTML = '';
        if (products.length === 0) {
            const row = tbody.insertRow();
            row.innerHTML = `<td colspan="9" style="text-align: center; padding: 20px;">ບໍ່ມີຂໍ້ມູນສິນຄ້າ</td>`;
            return;
        }

        products.forEach((p, i) => {
            const imgTag = p.image_path ? `<img src="${p.image_path}" alt="${p.name}" class="product-img">` : '<span>ບໍ່ມີຮູບ</span>';
            const row = tbody.insertRow();
            row.innerHTML = `
                <td data-label="ລຳດັບ">${i + 1}</td>
                <td data-label="ຮູບພາບ" style="text-align: center;">${imgTag}</td>
                <td data-label="ຊື່ສິນຄ້າ">${p.name}</td>
                <td data-label="ປະເພດສິນຄ້າ">${p.type}</td>
                <td data-label="ໝວດສິນຄ້າ">${p.category}</td>
                <td data-label="ຖານວາງ">${p.shelf}</td>
                <td data-label="ຈຳນວນ">${p.qty} ${p.unit}</td>
                <td data-label="ລາຄາຕໍ່ໜ່ວຍ">${p.price.toLocaleString()} ກິບ</td>
                <td data-label="ຈັດການ" style="text-align: center;">
                    <div class="action-buttons">
                        <button class="btn-edit" onclick="editProduct(${p.id})">ແກ້ໄຂ</button>
                        <button class="btn-delete" onclick="deleteProduct(${p.id})">ລົບ</button>
                    </div>
                </td>
            `;
        });
    }

    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', this.productId.value ? 'edit' : 'add');

        fetch('product_api.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    products = res.products;
                    renderTable();
                    closeModal();
                    alert('ບັນທຶກສຳເລັດແລ້ວ');
                } else {
                    alert(res.error || 'Error');
                }
            })
            .catch(() => alert('Network error'));
    });

    window.onclick = e => {
        if (e.target === document.getElementById('productModal')) closeModal();
    };

    document.addEventListener('DOMContentLoaded', renderTable);
</script>
</body>
</html>