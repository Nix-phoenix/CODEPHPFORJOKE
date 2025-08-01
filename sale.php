<?php
require_once 'includes/auth.php';
require_once 'db/connection.php';

// Simplified initial data fetch
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$like_search = "%$search%";
// Fetch dropdown data
$query = "SELECT
    s.s_id,
    s.date,
    c.c_name,
    p.p_id,
    p.p_name,
    p.category,
    p.type,
    p.unit,
    sd.qty,
    sd.price AS unit_price,
    sd.total_price
FROM Sell s
JOIN SellDetail sd ON s.s_id = sd.s_id
JOIN Product p ON sd.p_id = p.p_id
LEFT JOIN Customer c ON c.c_id = s.c_id 
WHERE c.c_name LIKE ? OR p.p_name LIKE ? OR s.s_id LIKE ?
ORDER BY s.s_id DESC LIMIT 100";

$stmt = $conn->prepare($query);
$stmt->bind_param('sss', $like_search, $like_search, $like_search);
$stmt->execute();
$sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); 
$customers = $conn->query("SELECT c_id, c_name FROM Customer ORDER BY c_name ASC")->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຈັດການຂາຍສິນຄ້າ</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="dashboard-container">
        <h2 class="dashboard-title">ຈັດການການຂາຍ</h2>
        <form method="get" style="display:flex;gap:12px;margin-bottom:18px;">
            <input type="text" name="search" placeholder="ຄົ້ນຫາ (ບິນ, ລູກຄ້າ, ສິນຄ້າ)" value="<?php echo htmlspecialchars($search); ?>" style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="dashboard-add-btn">ຄົ້ນຫາ</button>
        </form>
        <button type="button" class="dashboard-add-btn" onclick="openAddModal()" style="margin-bottom: 18px;">ເພີ່ມການຂາຍໃໝ່</button>

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ເລກທີຂາຍ</th>
                    <th>ວັນທີ</th>
                    <th>ຊື່ລູກຄ້າ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ປະເພດ</th>
                    <th>ຊະນິດ</th>
                    <th>ຫນ່ວຍ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາຕໍ່ຫນ່ວຍ</th>
                    <th>ລາຄາລວມ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody id="saleTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Sale Modal -->
<div id="saleModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 class="modal-title" id="modalTitle"></h3>
        <form id="saleForm">
            <input type="hidden" name="s_id" id="s_id">
            <label for="date">ວັນທີ:</label>
            <input type="date" id="date" name="date" class="gpg-input" required>
            <label for="customer">ຊື່ລູກຄ້າ:</label>
            <select id="customer" name="customer_name" class="gpg-input" required><?php foreach($customers as $c){echo "<option value='{$c['c_name']}'>{$c['c_name']}</option>";} ?></select>
            
            <label for="p_name">ຊື່ສິນຄ້າ (ເລືອກຈາກລາຍການ):</label>
            <select id="p_name" name="product_name" class="gpg-input" required onchange="updateUnitPrice()">
            </select>
            
            <label for="qty">ຈຳນວນ:</label>
            <input type="number" id="qty" name="qty" class="gpg-input" min="1" required onchange="calculateTotal()">
            
            <label for="unit_price">ລາຄາຕໍ່ຫນຶ່ງ:</label>
            <input type="number" id="unit_price" name="unit_price" class="gpg-input" min="0" step="0.01" required onchange="calculateTotal()">
            
            <label for="total_price">ລາຄາລວມ:</label>
            <input type="number" id="total_price" name="total_price" class="gpg-input" readonly>
            
            <div style="text-align:center;margin-top:20px;">
                <button type="submit" class="dashboard-add-btn">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<script>

    let sales = <?php echo json_encode($sales, JSON_UNESCAPED_UNICODE); ?>; 
    let products = []; // To be fetched
    
    function calculateTotal() {
        const qty = parseFloat(document.getElementById('qty').value) || 0;
        const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
        const totalPrice = qty * unitPrice;
        document.getElementById('total_price').value = totalPrice.toFixed(2);
    }
    
    function updateUnitPrice() {
        const productSelect = document.getElementById('p_name');
        const selectedProduct = products.find(p => p.p_name === productSelect.value);
        if (selectedProduct) {
            document.getElementById('unit_price').value = selectedProduct.price;
            calculateTotal();
        }
    }

    function renderTable() {
        const tbody = document.getElementById('saleTableBody');
        tbody.innerHTML = '';
        if (!sales || sales.length === 0) {
            tbody.innerHTML = '<tr><td colspan="11" style="text-align:center;">ບໍ່ພົບຂໍ້ມູນ</td></tr>';
            return;
        }
        sales.forEach(s => {
            const tr = tbody.insertRow();
            tr.innerHTML = `
                <td>${s.s_id}</td> 
                <td>${new Date(s.date).toLocaleDateString('en-GB')}</td>
                <td>${s.c_name}</td>
                <td>${s.p_name}</td>
                <td>${s.category}</td>
                <td>${s.type}</td>
                <td>${s.unit}</td>
                <td>${s.qty}</td>
                <td>${parseFloat(s.unit_price).toLocaleString()} ກິບ</td>
                <td>${parseFloat(s.total_price).toLocaleString()} ກິບ</td>
                <td>
                    <button class="dashboard-edit-btn" onclick='openEditModal(${JSON.stringify(s)})'>ແກ້ໄຂ</button> 
                    <button class="dashboard-delete-btn" onclick="deleteSale(${s.s_id})">ລົບ</button>
                </td>
            `;
        });
    }

    function closeModal() {
        document.getElementById('saleModal').style.display = 'none';
    }

    async function fetchProducts() {
        try {
            const response = await fetch('sale_api.php?action=getProducts');
            const data = await response.json();
            if (data.products) {
                products = data.products;
                const productSelect = document.getElementById('p_name');
                productSelect.innerHTML = '<option value="">-- ເລືອກສິນຄ້າ --</option>';
                products.forEach(p => {
                    productSelect.innerHTML += `<option value="${p.p_name}" data-price="${p.price}">${p.p_name} (Stock: ${p.qty}) - ${p.price} ກີບັບ</option>`;
                });
            } else {
                console.error("Could not fetch products");
            }
        } catch (e) {
            console.error("Could not fetch products", e);
        }
    }
    
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'ເພີ່ມການຂາຍໃໝ່';
        document.getElementById('saleForm').reset();
        document.getElementById('s_id').value = '';
        document.getElementById('date').valueAsDate = new Date(); 
        document.getElementById('saleModal').style.display = 'block';
    }


    function openEditModal(sale) {
        document.getElementById('modalTitle').textContent = 'ເບາດຈະກາຍານກາຍບາ່ງ';
        document.getElementById('saleForm').reset(); 
        console.log(sale);
        document.getElementById('s_id').value = sale.s_id;
        document.getElementById('date').value = sale.date.split(' ')[0]; // Format date for input
        document.getElementById('customer').value = sale.c_name;
        document.getElementById('p_name').value = sale.p_name;
        document.getElementById('qty').value = sale.qty;
        document.getElementById('unit_price').value = sale.unit_price;
        document.getElementById('total_price').value = sale.total_price;
        document.getElementById('saleModal').style.display = 'block';
    }

    function deleteSale(s_id) {
        if (!confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບລາຍການຂາຍນີ້? This will restore stock.')) return;

        fetch('sale_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id: s_id })
        }).then(r => r.json()).then(res => {
            if (res.success) {
                sales = res.sales; // Assuming the API returns updated sales data
                renderTable();
            } else {
                alert(res.error || 'Error deleting sale.');
            }
        });
    }
    
    document.getElementById('saleForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Create form data object with all required fields
        const formData = {
            s_id: document.getElementById('s_id').value || null,
            date: document.getElementById('date').value,
            customer_name: document.getElementById('customer').value,
            product_name: document.getElementById('p_name').value,
            qty: document.getElementById('qty').value,
            unit_price: document.getElementById('unit_price').value,
            total_price: document.getElementById('total_price').value,
            action: document.getElementById('s_id').value ? 'edit' : 'add'
        };

        // Log the data being sent for debugging
        console.log('Submitting form data:', formData);

        fetch('sale_api.php', {
            method: 'POST', 
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(res => {
            console.log('API Response:', res);
            if (res.success) {
                sales = res.sales || [];
                renderTable();
                closeModal();
            } else {
                alert(res.error || 'An error occurred while saving the sale.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    });

    window.onclick = e => {
        if (e.target === document.getElementById('saleModal')) closeModal(); };
    
    document.addEventListener('DOMContentLoaded', () => {
        renderTable();
        fetchProducts();
    });

</script>
</body>

</html>