<?php
include 'config.php';  // include config สำหรับเชื่อมต่อฐานข้อมูล
include 'navbar.php';




if (!isset($_SESSION['user_id'])) {
    // หากผู้ใช้ยังไม่ได้ล็อกอิน ให้ส่งพวกเขาไปยังหน้าเข้าสู่ระบบ
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];  // ดึง user_id จาก session

// บันทึกข้อมูลการเติมเงินลงในตาราง money
$sql = "INSERT INTO money (mon_price, mon_photho, user_id) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dsi", $amount, $target_file, $user_id);


// ตรวจสอบว่าฟอร์มถูกส่งหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];  // ดึง user_id จากฟอร์มที่ส่งมา
    $amount = $_POST['amount'];  // ดึงจำนวนเงินจากฟอร์ม
    
    // ตรวจสอบว่ามีการแนบไฟล์สลิปการโอนหรือไม่
    if (isset($_FILES['slip']) && $_FILES['slip']['error'] == 0) {
        $upload_dir = 'uploads/slips/';  // กำหนดไดเรกทอรีที่เก็บไฟล์สลิป
        
        // ตรวจสอบว่าไดเรกทอรีสำหรับอัปโหลดมีอยู่หรือไม่ หากไม่มีให้สร้าง
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);  // สร้างโฟลเดอร์และกำหนดสิทธิ์
        }
        
        $file_name = basename($_FILES['slip']['name']);
        $target_file = $upload_dir . $file_name;
        
        // ตรวจสอบชนิดไฟล์ที่อนุญาต
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array('jpg', 'jpeg', 'png', 'pdf');
        
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES['slip']['tmp_name'], $target_file)) {
                // บันทึกข้อมูลการเติมเงินลงในตาราง money
                $sql = "INSERT INTO money (mon_price, mon_photho, user_id) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("dsi", $amount, $target_file, $user_id);
                
                if ($stmt->execute()) {
                    echo "";
                } else {
                    echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์');</script>";
            }
        } else {
            echo "<script>alert('อนุญาตเฉพาะไฟล์ประเภท JPG, JPEG, PNG และ PDF');</script>";
        }
    } else {
        echo "<script>alert('กรุณาแนบไฟล์สลิปการโอน');</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เติมเงิน</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #4A148C; /* พื้นหลังสีม่วง */
            font-family: Arial, sans-serif;
        }

        /* Navbar */
        nav {
            position: fixed;
            top: 0;
            width: 100%;
            background-color: #333;
            padding: 10px 20px;
            z-index: 1000;
            text-align: center;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 15px;
            margin-right: 10px;
            font-size: 18px;
        }

        nav a:hover {
            background-color: #575757;
        }

        .page-title {
            text-align: center;
            font-size: 43px;
            font-weight: bold;
            color: #FFD700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-top: 100px; /* เพิ่มระยะห่างจาก navbar */
            margin-bottom: 30px;
        }

        .form-container {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 40px;
            width: 400px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            margin: 80px auto 0 auto; /* เพิ่ม margin-top เพื่อขยับฟอร์มลงมา */
        }

        .form-container h2 {
            color: #FFD700;
            text-align: center;
            font-size: 36px;
            margin-bottom: 30px;
        }

        .form-container label {
            display: block;
            color: #4A148C;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .form-container input[type="number"],
        .form-container input[type="file"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-container input[type="submit"] {
            width: 100%;
            background-color: #FFD700;
            color: #4A148C;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 50px;
        }

        .form-container input[type="submit"]:hover {
            background-color: #E1B700;
        }

        .bank-details {
            text-align: center;
            margin-bottom: 30px;
        }

        .bank-details h3 {
            font-size: 22px;
            color: #4A148C;
            margin-bottom: 10px;
        }

        .bank-details p {
            font-size: 18px;
            color: #333;
        }

    </style>
</head>
<body>

  
    <!-- Form Section -->
    <div class="form-container">
        <h2>เติมเงิน</h2>

        <div class="bank-details">
            <h3>เลขที่บัญชีที่ต้องโอนไป</h3>
            <p>1234567891234 ธนาคารกรุงไทย</p>
        </div>

        <form id="paymentForm" action="" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">  <!-- ดึงค่า user_id จาก session -->
    
    <label for="amount">จำนวนเงินที่ต้องการโอน (บาท):</label>
    <input type="number" id="amount" name="amount" placeholder="กรุณากรอกจำนวนเงิน" required>
    
    <label for="slip">แนบสลิปการโอน:</label>
    <input type="file" id="slip" name="slip" accept=".jpg, .jpeg, .png, .pdf" required>
    
    <input type="submit" value="ยืนยันการโอนเงิน">
</form>

    </div>


    <script>
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault(); // ป้องกันไม่ให้ส่งฟอร์มโดยอัตโนมัติ
    
    // แสดง SweetAlert แจ้งเตือนก่อนการยืนยัน
    Swal.fire({
        title: 'ยืนยันการโอนเงิน',
        text: 'คุณแน่ใจหรือไม่ว่าต้องการโอนเงิน?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ใช่, ยืนยัน!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            // หากผู้ใช้กดยืนยัน ให้ส่งฟอร์ม
            document.getElementById('paymentForm').submit();
        }
    });
});
</script>


</body>


</html>
