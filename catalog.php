<?php

require('navbar.php');
require('config.php');

// ตรวจสอบและเริ่ม session ถ้ายังไม่มี
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้ว
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ฟังก์ชันสำหรับดึงข้อมูลรายการสินค้าใน Catalog ของผู้ใช้
function getUserCatalogItems($user_id) {
    global $conn;
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    $sql = "SELECT c.cat_id, p.pro_name, p.pro_photo, c.pro_id
            FROM catalog c 
            JOIN product p ON c.pro_id = p.pro_id
            WHERE c.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
    return $items;
}
// ฟังก์ชันลบรายการของรางวัลออกจาก Catalog
function deleteCatalogItem($cat_id) {
    global $conn;
    $sql = "DELETE FROM catalog WHERE cat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cat_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// ตรวจสอบว่ามีคำขอลบรายการหรือไม่
if (isset($_GET['delete'])) {
    $cat_id = $_GET['delete'];
    
    if (deleteCatalogItem($cat_id)) {
        echo "<script>alert('ลบรายการเรียบร้อยแล้ว');</script>";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('ไม่สามารถลบรายการได้');</script>";
    }
}

?>


<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แสดงรายการของรางวัลที่สุ่มได้</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #4A148C;
            color: white;
            font-family: Arial, sans-serif;
        }
        .page-title {
            text-align: center;
            font-size: 43px;
            font-weight: bold;
            color: #FFD700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .table-container {
            width: 80%;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            color: #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
            text-align: center;
            padding: 10px;
        }
        th {
            background-color: #4A148C;
            color: white;
        }
        td img {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .product-name {
            font-size: 18px;
            font-weight: bold;
            color: #4A148C;
        }
        .delete-btn {
            background-color: #FF6347;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .delete-btn:hover {
            background-color: #FF4500;
        }
        .print-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            float: right;
            margin-bottom: 10px;
            text-decoration: none;
        }
        .print-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="page-title">
        รายการของรางวัลที่สุ่มได้
    </div>

    <div class="table-container">
    <a href="generate_pdf01.php" class="print-btn">พิมพ์ PDF</a>
        <table>
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>รูปภาพสินค้า</th>
                    
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
            <?php
           $catalogItems = getUserCatalogItems($user_id);
           if (count($catalogItems) > 0): ?>
               <?php foreach ($catalogItems as $item): ?>
                   <tr>
                       <td class="product-name"><?php echo htmlspecialchars($item['pro_name']); ?></td>
                       <td><img src="./uploads/<?php echo htmlspecialchars($item['pro_photo']); ?>" alt="<?php echo htmlspecialchars($item['pro_name']); ?>"></td>
                       <td>
                           <a href="?delete=<?php echo $item['cat_id']; ?>" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบ?')">
                               <button class="delete-btn">ลบ</button>
                           </a>
                       </td>
                   </tr>
               <?php endforeach; ?>
           <?php else: ?>
                <tr>
                    <td colspan="3">ไม่มีรายการของรางวัลที่สุ่มได้</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
       
    </div>
</body>
</html>
