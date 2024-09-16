<?php
include 'config.php';

// Query เพื่อดึงข้อมูลจำนวนผู้ใช้
$query_users = "SELECT COUNT(*) AS total_users FROM user";
$result_users = mysqli_query($conn, $query_users);
$row_users = mysqli_fetch_assoc($result_users);
$total_users = $row_users['total_users'];

// Query เพื่อดึงข้อมูลจำนวนสินค้า
$query_products = "SELECT COUNT(*) AS total_products FROM product";
$result_products = mysqli_query($conn, $query_products);
$row_products = mysqli_fetch_assoc($result_products);
$total_products = $row_products['total_products'];

// Query เพื่อดึงข้อมูลอัตราการสุ่ม พร้อมชื่อสินค้า
$query_rates = "
    SELECT sr.rate_id, sr.rate, p.pro_name 
    FROM sampling_rate sr
    JOIN product p ON sr.pro_id = p.pro_id";
$result_rates = mysqli_query($conn, $query_rates);

// คำนวณ totalRate เพื่อใช้คำนวณเปอร์เซ็นต์
$totalRateQuery = "SELECT SUM(rate) AS totalRate FROM sampling_rate";
$totalRateResult = mysqli_query($conn, $totalRateQuery);
$totalRateRow = mysqli_fetch_assoc($totalRateResult);
$totalRate = $totalRateRow['totalRate'];

// Query เพื่อดึงข้อมูลการเติมเงิน
$query_payments = "SELECT mon_price, user_id FROM money"; 
$result_payments = mysqli_query($conn, $query_payments);

$payment_data = [];
while ($row = mysqli_fetch_assoc($result_payments)) {
    $payment_data[] = $row;
}

// Query เพื่อดึงข้อมูลสินค้าที่ไม่มีเรทสุ่ม
$query_no_rate_products = "
    SELECT p.pro_id, p.pro_name 
    FROM product p
    LEFT JOIN sampling_rate sr ON p.pro_id = sr.pro_id
    WHERE sr.pro_id IS NULL";  // สินค้าที่ไม่มีการกำหนดเรทสุ่ม
$result_no_rate_products = mysqli_query($conn, $query_no_rate_products);

$no_rate_products = [];
while ($row = mysqli_fetch_assoc($result_no_rate_products)) {
    $no_rate_products[] = $row;
}

// ส่งข้อมูลไปยัง JavaScript ในรูปแบบ JSON
$payment_data_json = json_encode($payment_data);
$no_rate_products_json = json_encode($no_rate_products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานข้อมูลระบบ</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            display: flex;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar .menu-group {
            margin-bottom: 20px;
            border-bottom: 2px solid #1abc9c;
            padding-bottom: 0;
        }
        .sidebar p {
            margin-bottom: 0;
            padding-bottom: 5px;
        }
        .sidebar a {
            color: white;
            padding: 15px 20px;
            text-decoration: none;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #34495e;
            text-align: center;
        }
        .sidebar a:hover {
            background: #1abc9c;
        }
        .container {
            margin-left: 290px;
            padding: 20px;
            background: #ecf0f1;
            flex: 1;
            height: auto;
        }
        h2 {
            margin-top: 0;
        }
        .btn-submit {
            display: inline-block;
            padding: 10px 20px;
            color: white;
            background: #2ecc71;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #bdc3c7;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #34495e;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .sidebar a.btn-logout {
            background: #e74c3c; 
            color: white; 
            padding: 15px 20px;
            text-decoration: none;
            display: block;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        .sidebar a.btn-logout:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>เมนู</h2>
    <br>
    <div class="menu-group">
        <p>จัดการข้อมูลพื้นฐาน</p>
    </div>
    
    <div class="menu-group">
        <a href="Sampling_rate.php">ข้อมูลอัตราการสุ่ม</a>
        <a href="product.php">ข้อมูลสินค้า</a>
        <a href="money_admin.php">ข้อมูลการเติมเงิน</a>
        <a href="admin_01.php">รายงาน</a>
    </div>
  
    <a href="logout.php" class="btn-logout" onclick="return confirm('คุณแน่ใจว่าต้องการออกจากระบบหรือไม่?');">ออกจากระบบ</a>
</div>

<div class="container">
    <h2>รายงานข้อมูลระบบ</h2>

    <p>จำนวนผู้ใช้ทั้งหมด: <?php echo $total_users; ?> คน</p>
    <p>จำนวนสินค้าทั้งหมด: <?php echo $total_products; ?> ชิ้น</p>
    <a href="generate_pdf.php" class="btn-submit">ดาวน์โหลดรายงานเป็น PDF</a>

    <h3>ข้อมูลอัตราการสุ่ม</h3>
    <table>
        <tr>
            <th>รหัสเรท</th>
            <th>ชื่อสินค้า</th>
            <th>รายละเอียด (%)</th>
        </tr>
        <?php while($row_rates = mysqli_fetch_assoc($result_rates)) { 
            // คำนวณเปอร์เซ็นต์ของแต่ละเรท
            if ($totalRate > 0) {
                $percentage = ($row_rates['rate'] / $totalRate) * 100;
            } else {
                $percentage = 0;
            }
        ?>
        <tr>
            <td><?php echo $row_rates['rate_id']; ?></td>
            <td><?php echo htmlspecialchars($row_rates['pro_name']); ?></td>
            <td><?php echo number_format($percentage, 2); ?>%</td>
        </tr>
        <?php } ?>
    </table>

    <h3>กราฟข้อมูลการเติมเงิน</h3>
    <canvas id="paymentChart" width="400" height="200"></canvas>

    <h3>กราฟสินค้าที่ไม่ได้แสดงในอัตราการสุ่ม</h3>
    <canvas id="noRateProductsChart" width="400" height="200"></canvas>

    <script>
        // กราฟการเติมเงิน
        const paymentData = <?php echo $payment_data_json; ?>;
        const labels = paymentData.map(payment => `User ID: ${payment.user_id}`);
        const data = paymentData.map(payment => payment.mon_price);

        const ctx = document.getElementById('paymentChart').getContext('2d');
        const paymentChart = new Chart(ctx, {
            type: 'bar', 
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนเงินที่เติม (บาท)',
                    data: data,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'User ID'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนเงินที่เติม (บาท)'
                        }
                    }
                }
            }
        });

        // กราฟสินค้าที่ไม่มีเรทสุ่ม
        const noRateProducts = <?php echo $no_rate_products_json; ?>;
        const noRateLabels = noRateProducts.map(product => product.pro_name);
        const noRateData = noRateProducts.map(() => 1);

        const ctxNoRate = document.getElementById('noRateProductsChart').getContext('2d');
        const noRateProductsChart = new Chart(ctxNoRate, {
            type: 'bar',
            data: {
                labels: noRateLabels,
                datasets: [{
                    label: 'สินค้าที่ไม่มีการตั้งค่าเรทสุ่ม',
                    data: noRateData,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'ชื่อสินค้า'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'จำนวนสินค้า'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>

</div>

</body>
</html>
