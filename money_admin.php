<?php
include 'config.php';

// ตรวจสอบว่ามีการแก้ไขข้อมูลหรือไม่
if (isset($_POST['edit'])) {
    $mon_id = $_POST['mon_id'];
    $mon_price = $_POST['mon_price'];

    // อัปเดตข้อมูล mon_price ในตาราง money
    $sql_update = "UPDATE money SET mon_price = ? WHERE mon_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("di", $mon_price, $mon_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('แก้ไขข้อมูลสำเร็จ');</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการแก้ไขข้อมูล');</script>";
    }

    $stmt->close();
}
// ตรวจสอบว่ามีการลบข้อมูลหรือไม่
if (isset($_POST['delete'])) {
    $mon_id = $_POST['mon_id'];

    // ลบข้อมูลจากตาราง money
    $sql_delete = "DELETE FROM money WHERE mon_id = ?";
    $stmt = $conn->prepare($sql_delete);
    $stmt->bind_param("i", $mon_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('ลบข้อมูลสำเร็จ');</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการลบข้อมูล');</script>";
    }

    $stmt->close();
}

// ดึงข้อมูลจากตาราง money พร้อมชื่อผู้ใช้จากตาราง user
$sql = "SELECT money.*, user.user_name AS user_name FROM money JOIN user ON money.user_id = user.user_id";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลการเติมเงิน</title>
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
    <h2>ข้อมูลการเติมเงิน</h2>

    <table>
        <thead>
            <tr>
             
                <th>จำนวนเงิน</th>
                <th>รูปสลิป</th>
                <th>User ID</th>
                
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                
                <td>
                    <form action="" method="POST">
                        <input type="hidden" name="mon_id" value="<?php echo $row['mon_id']; ?>">
                        <input type="number" name="mon_price" value="<?php echo $row['mon_price']; ?>">
                        <input type="submit" name="edit" value="แก้ไข" class="btn-edit">
                        <input type="submit" name="delete" value="ลบ" class="btn-delete" onclick="return confirm('คุณแน่ใจว่าต้องการลบข้อมูลนี้หรือไม่?');">
                    </form>
                </td>
                <td><img src="<?php echo $row['mon_photho']; ?>" alt="slip" width="100"></td>
                <td><?php echo $row['user_name']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
