<?php
include 'config.php';

// ตัวแปรสำหรับการแจ้งเตือน
$message = '';
$error = '';

// เพิ่มข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    // แปลงเปอร์เซ็นต์เป็นเรทจริง (หาร 100)
    $rate = $_POST['rate'] / 100;
    $pro_id = $_POST['pro_id'];

    $sql = "INSERT INTO sampling_rate (rate, pro_id) VALUES ('$rate', '$pro_id')";
    if ($conn->query($sql) === TRUE) {
        $message = "เพิ่มข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// ลบข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {
    $rate_id = $_GET['delete'];

    $sql = "DELETE FROM sampling_rate WHERE rate_id=$rate_id";
    if ($conn->query($sql) === TRUE) {
        $message = "ลบข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// แก้ไขข้อมูล
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $rate_id = $_POST['rate_id'];
    // แปลงเปอร์เซ็นต์เป็นเรทจริง (หาร 100)
    $rate = $_POST['rate'] / 100;

    $sql = "UPDATE sampling_rate SET rate='$rate' WHERE rate_id=$rate_id";
    if ($conn->query($sql) === TRUE) {
        $message = "อัปเดตข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$products = [];
$sql = "SELECT pro_id, pro_name FROM product";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// ดึงข้อมูลอัตราการสุ่มและคำนวณเปอร์เซ็นต์
$samplingRates = [];
$totalRate = 0;

// คำนวณเรททั้งหมดเพื่อใช้ในการคำนวณเปอร์เซ็นต์
$sql = "SELECT SUM(rate) AS totalRate FROM sampling_rate";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $totalRow = $result->fetch_assoc();
    $totalRate = $totalRow['totalRate'];
}

// ดึงข้อมูลเรทของสินค้าแต่ละรายการ
// ดึงข้อมูลเรทของสินค้าแต่ละรายการ
$sql = "SELECT sr.rate_id, sr.rate, p.pro_name FROM sampling_rate sr 
        JOIN product p ON sr.pro_id = p.pro_id";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // ตรวจสอบว่า totalRate มากกว่า 0 ก่อนคำนวณ
        if ($totalRate > 0) {
            $percentage = ($row['rate'] / $totalRate) * 100;
        } else {
            $percentage = 0; // ถ้า totalRate เป็น 0 ให้กำหนดเป็น 0%
        }
        $row['percentage'] = $percentage;
        $samplingRates[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการอัตราการสุ่ม</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
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
        .message, .error {
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .message {
            background: #2ecc71;
            color: white;
        }
        .error {
            background: #e74c3c;
            color: white;
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
        .btn-submit, .btn-edit, .btn-delete {
            padding: 10px 15px;
            border-radius: 5px;
            color: white;
            text-decoration: none;
            text-align: center;
            display: inline-block;
            margin: 5px;
            cursor: pointer;
        }
        .btn-submit {
            background-color: #28a745;
            border: none;
        }
        .btn-submit:hover {
            background-color: #218838;
        }
        .btn-edit {
            background-color: #ffc107;
            border: none;
        }
        .btn-edit:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
            border: none;
        }
        .btn-delete:hover {
            background-color: #c82333;
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
    <h2>เพิ่มอัตราการสุ่ม</h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="Sampling_rate.php">
        <div class="form-group">
            <label for="pro_id">ชื่อสินค้า:</label>
            <select id="pro_id" name="pro_id" required>
                <option value="">เลือกสินค้า</option>
                <?php foreach($products as $product): ?>
                    <option value="<?php echo $product['pro_id']; ?>"><?php echo $product['pro_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="rate">อัตราการสุ่ม (%):</label>
            <input type="number" id="rate" name="rate" step="0.01" required>
        </div>
        <input type="hidden" name="action" value="add">
        <button type="submit" class="btn-submit">เพิ่มอัตราการสุ่ม</button>
    </form>

    <h2>รายการอัตราการสุ่ม</h2>
    <table>
        <tr>
            <th>เรทการสุ่ม (%)</th>
            <th>ชื่อสินค้า</th>
            <th>โอกาสการออก (%)</th>
            <th>การดำเนินการ</th>
        </tr>

        <?php foreach($samplingRates as $rate): ?>
            <tr>
                <td><?php echo number_format($rate["rate"] * 100, 2); ?>%</td>
                <td><?php echo $rate["pro_name"]; ?></td>
                <td><?php echo number_format($rate["percentage"], 2); ?>%</td>
                <td>
                    <form method="POST" action="Sampling_rate.php" style="display:inline;">
                        <input type="hidden" name="rate_id" value="<?php echo $rate["rate_id"]; ?>">
                        <input type="number" step="0.01" name="rate" value="<?php echo $rate["rate"] * 100; ?>" required>
                        <input type="hidden" name="action" value="update">
                        <button type="submit" class="btn-edit">แก้ไข</button>
                    </form>
                    <a class='btn btn-delete' href='Sampling_rate.php?delete=<?php echo $rate["rate_id"]; ?>' onclick="return confirm('คุณแน่ใจว่าต้องการลบข้อมูลนี้หรือไม่?');">ลบ</a>
                </td>
            </tr>
        <?php endforeach; ?>

        <?php if (empty($samplingRates)): ?>
            <tr><td colspan="4">ไม่มีข้อมูล</td></tr>
        <?php endif; ?>
    </table>
</div>

</body>
</html>