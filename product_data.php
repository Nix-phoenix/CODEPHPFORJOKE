<?php
include 'includes/auth.php';
include 'db/connection.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch products from database based on search
$stmt = $conn->prepare("SELECT p_id AS id, p_name AS name, category, type, shelf, unit, qty, price FROM Product WHERE p_name LIKE ? OR type LIKE ? ORDER BY p_id ASC");
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
    <title>ບັນທືກຂໍ້ມູນສິນຄ້າ - ລະບົບຈັດການຮ້ານGPG</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<?php include 'includes/navbar.php'; ?>

<div class="container">
    <div class="dashboard-container">
        <h2 class="dashboard-title" style="margin-bottom: 18px;">ບັນທືກຂໍ້ມູນສິນຄ້າ</h2>
        <!-- Search Bar -->
        <form method="get" style="display:flex;gap:12px;margin-bottom:18px;">
            <input type="text" name="search" placeholder="ຄົ້ນຫາ (ສິນຄ້າ, ໝວດ, ຍິ່ຫໍ້)" value="<?php echo htmlspecialchars($search); ?>" style="flex:1;padding:10px;border-radius:6px;border:1px solid #ccc;">
            <button type="submit" class="dashboard-add-btn" style="width:180px;">ຄົ້ນຫາ</button>
        </form>
        <button type="button" class="dashboard-add-btn" onclick="openAddModal()" style="margin-bottom: 18px; width: 200px;">ເພີ່ມຂໍ້ມູນສິນຄ້າ</button>

        <!-- Product Data Table -->
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>ລະຫັດ</th>
                    <th>ຊື່ສິນຄ້າ</th>
                    <th>ປະເພດສິນຄ້າ</th><th>ໝວດສິນຄ້າ</th>
                    <th>ຖານວາງ</th>
                    <th>ຫນ່ວຍ</th>
                    <th>ຈຳນວນ</th>
                    <th>ລາຄາຕໍ່ໜ່ວຍ</th>
                    <th>ຈັດການ</th>
                </tr>
            </thead>
            <tbody id="productTableBody"></tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3 class="modal-title" id="modalTitle">ເພີ່ມຂໍ້ມູນສິນຄ້າ</h3>
        <form id="productForm">
            <input type="hidden" id="productId" name="productId">
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
            <div style="text-align:center;margin-top:20px;">
                <button type="submit" class="dashboard-add-btn">ບັນທຶກ</button>
            </div>
        </form>
    </div>
</div>

<script>
    let products = <?php echo json_encode($products, JSON_UNESCAPED_UNICODE); ?>;

    function openAddModal(){
        document.getElementById('modalTitle').textContent='ເພີ່ມຂໍ້ມູນສິນຄ້າ';
        document.getElementById('productForm').reset();
        document.getElementById('productId').value='';
        document.getElementById('productModal').style.display='block';
    }
    function editProduct(id){
        const p = products.find(x=>x.id===id);
        if(!p) return;
        document.getElementById('modalTitle').textContent='ແກ້ໄຂຂໍ້ມູນສິນຄ້າ';
        document.getElementById('productId').value=p.id;
        document.getElementById('productName').value=p.name;
        document.getElementById('productCategory').value=p.category;
        document.getElementById('productType').value=p.type;
        document.getElementById('productShelf').value=p.shelf;
        document.getElementById('productUnit').value=p.unit;
        document.getElementById('productQty').value=p.qty;
        document.getElementById('productPrice').value=p.price;
        document.getElementById('productModal').style.display='block';
    }
    function deleteProduct(id){
        if(!confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຈະລົບ?')) return;
        fetch('product_api.php', {
            method: 'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({action:'delete', id:id})
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.success){
                products = res.products;
                renderTable();
            }else alert(res.error||'Error');
        })
        .catch(()=>alert('Network error'));
    }
    function closeModal(){document.getElementById('productModal').style.display='none';}
    function renderTable(){
        const tbody=document.getElementById('productTableBody');
        tbody.innerHTML='';
        products.forEach((p,i)=>{
            tbody.insertRow().innerHTML=`<td>${i+1}</td><td>${p.name}</td><td>${p.type}</td><td>${p.category}</td><td>${p.shelf}</td><td>${p.unit}</td><td>${p.qty}</td><td>${p.price.toLocaleString()} ກິບ</td><td><button class=\"dashboard-edit-btn\" onclick=\"editProduct(${p.id})\">ແກ້ໄຂ</button> <button class=\"dashboard-delete-btn\" onclick=\"deleteProduct(${p.id})\">ລົບ</button></td>`;
        });
    }
    document.getElementById('productForm').addEventListener('submit',function(e){
        e.preventDefault();
        const id=this.productId.value;
        const payload={
            action: id ? 'edit':'add',
            id: id? parseInt(id): undefined,
            name:this.productName.value,
            category:this.productCategory.value,
            type:this.productType.value,
            shelf:this.productShelf.value,
            unit:this.productUnit.value,
            qty:parseInt(this.productQty.value),
            price:parseFloat(this.productPrice.value)
        };
        fetch('product_api.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        })
        .then(r=>r.json())
        .then(res=>{
            if(res.success){
                products = res.products;
                renderTable();
                closeModal();
            }else alert(res.error||'Error');
        })
        .catch(()=>alert('Network error'));
    });
    window.onclick=e=>{if(e.target===document.getElementById('productModal')) closeModal();};
    document.addEventListener('DOMContentLoaded',renderTable);
</script>
</html>
