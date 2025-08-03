<?php 
 require_once 'includes/auth.php'; 
 require_once 'db/connection.php'; 

 // Fetch payment data from the database with corrected query 
 $payments = []; 
 // The SQL query is updated to calculate the change amount.
 // We use COALESCE to handle NULL values for amount_paid if there's no payment record.
 $sql = "SELECT 
             s.s_id, 
             c.c_name, 
             GROUP_CONCAT(p.p_name SEPARATOR ', ') as products, 
             SUM(sd.qty) as total_qty, 
             (SELECT SUM(total_price) FROM SellDetail WHERE s_id = s.s_id) AS price_total, 
             pm.customer_paid AS amount_paid, 
             ((SELECT SUM(total_price) FROM SellDetail WHERE s_id = s.s_id) - COALESCE(pm.customer_paid, 0)) AS due_amount,
             (COALESCE(pm.customer_paid, 0) - (SELECT SUM(total_price) FROM SellDetail WHERE s_id = s.s_id)) AS change_amount,
             pm.date AS payment_date
         FROM Sell s 
         JOIN SellDetail sd ON s.s_id = sd.s_id 
         JOIN Product p ON sd.p_id = p.p_id 
         LEFT JOIN Customer c ON s.c_id = c.c_id 
         LEFT JOIN Payment pm ON s.s_id = pm.s_id 
         GROUP BY s.s_id 
         ORDER BY s.date DESC"; 

 $result = $conn->query($sql); 

 if ($result === false) { 
     die("Database query failed: " . $conn->error); 
 } 

 if ($result->num_rows > 0) { 
     $payments = $result->fetch_all(MYSQLI_ASSOC); 
 } 

 if ($result) { 
     $result->free(); 
 } 

 function laoNumberFormat($value) { 
     $numeric_value = is_numeric($value) ? (float)$value : 0; 
     return number_format($numeric_value, 0, '.', ',') . ' ກິບ'; 
 } 
 ?> 
 <!DOCTYPE html> 
 <html lang="lo"> 
 <head> 
     <meta charset="UTF-8"> 
     <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
     <title>ລາຍງານການຈັດການການຊຳລະ</title> 
     <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;700&display=swap" rel="stylesheet"> 
     <link rel="stylesheet" href="assets/css/style.css"> 
     <style> 
         @media print { 
             .no-print, .gpg-navbar { display: none !important; } 
             body { margin: 0; } 
         } 
         .receipt-container { 
             max-width: 800px; 
             margin: 0 auto; 
             background: #fff; 
             padding: 10px 30px; 
             font-family: 'Noto Sans Lao', sans-serif; 
         } 
         .receipt-title { 
             text-align: center; 
             font-size: 32px; 
             font-weight: 700; 
             margin-bottom: 4px; 
         } 
         .receipt-header { 
             display: flex; 
             justify-content: space-between; 
             margin-top: 10px; 
         } 
         .receipt-header h3 { 
             font-size: 34px; 
             font-weight: 700; 
             margin: 0; 
         } 
         .store-info p{margin:0;font-size:14px;} 
         .right-info{ text-align:right; font-size:14px;} 
         .dotted{display:inline-block;min-width:100px;border-bottom:1px dotted #000;margin-left:6px;} 
         .receipt-table, .receipt-table th, .receipt-table td { 
             border: 1px solid #000; 
             border-collapse: collapse; 
         } 
         .receipt-table { 
             width: 100%; 
             margin-top: 16px; 
         } 
         .receipt-table th, .receipt-table td { 
             padding: 6px 4px; 
             text-align: center; 
             font-size: 11px; 
         } 
         .receipt-table td.text-left { 
             text-align: left; 
         } 
         .receipt-footer { 
             margin-top: 24px; 
             display: flex; 
             justify-content: space-between; 
         } 
         .Print-btn { 
             background: #4caf50; 
             color: #fff; 
             border: none; 
             border-radius: 8px; 
             padding: 12px 24px; 
             font-size: 1.05rem; 
             cursor: pointer; 
             transition: background 0.2s; 
         } 
         .Print-btn:hover { background: #388e3c; } 
     </style> 
 </head> 
 <body> 
 <?php include 'includes/navbar.php'; ?> 

 <div class="receipt-container"> 
     <div class="container no-print" style="text-align:right;margin-top:20px;"> 
         <button class="Print-btn" onclick="window.print();">ພິມລາຍງານ</button> 
     </div> 
      
     <h2 class="receipt-title">ໃບລາຍງານການຈັດການການຊຳລະ<br>Payment Management Report Receipt</h2> 

     <div class="receipt-header"> 
         <div class="store-info"> 
             <h3>ຮ້ານ ຈິພິຈີ</h3> 
             <p style="font-weight:bold;">ສະຫນອງອຸປະປະກອນແລະທໍ່ນໍ້າປະປາທຸກຊະນິດ<br> 
             <p style="font-weight:bold; margin-left:50px;">ໂທ 020-58828288</p> 
             <p style="font-weight:bold;">WhatsApp 030-5656555<br>Facebook @gpglaosstore</p> 
         </div> 
         <div class="right-info"> 
             <p style="font-weight:bold; margin-right:129px;">ທີ່ຢູ່ ບ້ານໂພນຕ້ອງ</p> 
             <p style="font-weight:bold;">ເມືອງ ຈັນທະບູລີ ແຂວງ ນະຄອນຫຼວງວຽງຈັນ</p> 
             <p>ເລກທີ:<span class="dotted"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span></p> 
             <p>ວັນທີ:<span class="dotted"><?php echo date('d/m/Y'); ?></span></p> 
         </div> 
     </div> 
      <p style="margin-top:12px;">ທີ່ຢູ່: ບ້ານ ນາຄວາຍ, ເມືອງ ສີສັດຕະນາກ, ນະຄອນຫຼວງວຽງຈັນ</p> 

     <table class="receipt-table"> 
         <thead> 
             <tr> 
                 <th>ເລກທີ່ບິນ</th> 
                 <th>ຊື່ລູກຄ້າ</th> 
                 <th>ຊື່ສິນຄ້າ</th> 
                 <th>ຈຳນວນ</th> 
                 <th>ລາຄາທັງໝົດ</th> 
                 <th>ຈຳນວນເງິນລູກຄ້າຈ່າຍ</th>
                 <th>ເງິນທອນ</th>
                 <th>ວັນທີຈ່າຍ</th> 
             </tr> 
         </thead> 
         <tbody> 
             <?php  
             if (!empty($payments)): 
                 foreach ($payments as $payment): 
             ?> 
                 <tr> 
                     <td><?php echo htmlspecialchars($payment['s_id']); ?></td> 
                     <td class="text-left"><?php echo htmlspecialchars($payment['c_name'] ?? 'N/A'); ?></td> 
                     <td class="text-left"><?php echo htmlspecialchars($payment['products']); ?></td> 
                     <td><?php echo htmlspecialchars($payment['total_qty']); ?></td> 
                     <td><?php echo laoNumberFormat($payment['price_total']); ?></td> 
                     <td><?php echo laoNumberFormat($payment['amount_paid']); ?></td>
                     <td><?php echo laoNumberFormat($payment['change_amount']); ?></td>
                     <td><?php echo $payment['payment_date'] ? date('d/m/Y', strtotime($payment['payment_date'])) : 'N/A'; ?></td> 
                 </tr> 
             <?php  
                 endforeach; 
             else:  
             ?> 
                 <tr> 
                     <td colspan="9">ບໍ່ມີຂໍ້ມູນການຊຳລະ.</td> 
                 </tr> 
             <?php endif; ?> 
         </tbody> 
     </table> 

     <div class="receipt-footer"> 
         <div style="text-align:center; font-weight:bold;"> 
             <p>ເຈົ້າຂອງຮ້ານ<br>Owner</p> 
         </div> 
         <div style="text-align:center; font-weight:bold;"> 
             <p>ຜູ້ຮັບເງິນ<br>Cashier</p> 
         </div> 
         <div style="text-align:center;"> 
             <p style="text-align:right;font-style:italic;">ຂອບໃຈທີ່ໃຊ້ບໍລິການ</p> 
         </div> 
     </div> 
 </div> 

 </body> 
 </html> 
