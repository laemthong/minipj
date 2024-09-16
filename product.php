<?php
include 'config.php';

$message = '';
$error = '';

// เพิ่มข้อมูลสินค้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $pro_name = $_POST['pro_name'];
    $pro_photo = '';

    // ตรวจสอบว่ามีสินค้าชื่อเดียวกันอยู่ในระบบหรือไม่
    $check_sql = "SELECT * FROM product WHERE pro_name = '$pro_name'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // ถ้าพบข้อมูลซ้ำ
        $error = "มีสินค้าชื่อ '$pro_name' อยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น";
    } else {
        // อัปโหลดไฟล์ภาพ
        if (isset($_FILES['pro_photo']) && $_FILES['pro_photo']['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['pro_photo']['name']);
            if (move_uploaded_file($_FILES['pro_photo']['tmp_name'], $target_file)) {
                $pro_photo = $_FILES['pro_photo']['name'];
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
            }
        }

        if (!$error) {
            $sql = "INSERT INTO product (pro_name, pro_photo) VALUES ('$pro_name', '$pro_photo')";
            if ($conn->query($sql) === TRUE) {
                $message = "เพิ่มข้อมูลสำเร็จ";
                header("Location: product.php"); // รีเฟรชหน้าเว็บหลังจากเพิ่มข้อมูลสำเร็จ
                exit();
            } else {
                $error = "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    }
}

// แก้ไขข้อมูลสินค้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $pro_id = $_POST['pro_id'];
    $pro_name = $_POST['pro_name'];
    $pro_photo = '';

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่หรือไม่
    if (isset($_FILES['pro_photo']) && $_FILES['pro_photo']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['pro_photo']['name']);
        if (move_uploaded_file($_FILES['pro_photo']['tmp_name'], $target_file)) {
            $pro_photo = $_FILES['pro_photo']['name'];
            $sql = "UPDATE product SET pro_name='$pro_name', pro_photo='$pro_photo' WHERE pro_id=$pro_id";
        } else {
            $error = "เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
        }
    } else {
        $sql = "UPDATE product SET pro_name='$pro_name' WHERE pro_id=$pro_id";
    }

    if (!$error && $conn->query($sql) === TRUE) {
        $message = "แก้ไขข้อมูลสำเร็จ";
        header("Location: product.php"); // รีเฟรชหน้าเว็บหลังจากแก้ไขข้อมูลสำเร็จ
        exit();
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// ลบข้อมูลสินค้า
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {
    $pro_id = $_GET['delete'];

    // ลบรูปภาพที่เกี่ยวข้องก่อน
    $sql = "SELECT pro_photo FROM product WHERE pro_id=$pro_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (file_exists("uploads/" . $row['pro_photo'])) {
            unlink("uploads/" . $row['pro_photo']); // ลบไฟล์รูปภาพ
        }
    }

    $sql = "DELETE FROM product WHERE pro_id=$pro_id";
    if ($conn->query($sql) === TRUE) {
        $message = "ลบข้อมูลสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาด: " . $conn->error;
    }
}

// แก้ไขข้อมูล (ดึงข้อมูลสินค้าที่จะทำการแก้ไขมาแสดง)
$editing = false;
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['edit'])) {
    $pro_id = $_GET['edit'];
    $sql = "SELECT * FROM product WHERE pro_id=$pro_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $pro_name = $row['pro_name'];
        $pro_photo = $row['pro_photo'];
        $editing = true;
    } else {
        $error = "ไม่พบข้อมูลที่ต้องการแก้ไข";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลสินค้า</title>
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
    <h2><?php echo $editing ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่'; ?></h2>

    <?php if ($message) { echo "<div class='message'>$message</div>"; } ?>
    <?php if ($error) { echo "<div class='error'>$error</div>"; } ?>

    <form method="POST" action="product.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="pro_name">ชื่อสินค้า:</label>
            <input type="text" id="pro_name" name="pro_name" value="<?php echo isset($pro_name) ? $pro_name : ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="pro_photo">รูปภาพสินค้า:</label>
            <input type="file" id="pro_photo" name="pro_photo">
        </div>
        <?php if ($editing): ?>
            <?php if (!empty($pro_photo)): ?>
                <div class="form-group">
                    <label>รูปภาพปัจจุบัน:</label>
                    <img src="uploads/<?php echo $pro_photo; ?>" alt="<?php echo $pro_name; ?>" style="width:100px;">
                </div>
            <?php endif; ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="pro_id" value="<?php echo $pro_id; ?>">
            <button type="submit" class="btn-submit">บันทึกการเปลี่ยนแปลง</button>
        <?php else: ?>
            <input type="hidden" name="action" value="add">
            <button type="submit" class="btn-submit">เพิ่มสินค้า</button>
        <?php endif; ?>
    </form>

    <h2>รายการสินค้า</h2>

    <table>
        <tr>
            <th>ชื่อสินค้า</th>
            <th>รูปภาพ</th>
            <th>การดำเนินการ</th>
        </tr>

        <?php
        $sql = "SELECT * FROM product";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["pro_name"] . "</td>";
                echo "<td><img src='uploads/" . $row["pro_photo"] . "' alt='" . $row["pro_name"] . "' style='width:100px;'></td>";
                echo "<td>
                        <a class='btn btn-edit' href='product.php?edit=".$row["pro_id"]."'>แก้ไข</a>
                        <a class='btn btn-delete' href='product.php?delete=".$row["pro_id"]."' onclick='return confirm(\"คุณแน่ใจว่าต้องการลบข้อมูลนี้หรือไม่?\");'>ลบ</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>ไม่มีข้อมูล</td></tr>";
        }

        $conn->close();
        ?>

    </table>
</div>

</body>
</html>
