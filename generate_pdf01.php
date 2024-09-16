<?php
// Include TCPDF library
require_once('tcpdf/tcpdf.php');
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

// ฟังก์ชันสำหรับดึงข้อมูลผู้ใช้พร้อมยอดเครดิตรวม
function getUserInfo($user_id) {
    global $conn;
    $sql = "SELECT u.user_name, SUM(m.mon_price) AS total_credit 
            FROM user u
            LEFT JOIN money m ON u.user_id = m.user_id
            WHERE u.user_id = ?
            GROUP BY u.user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $userInfo = $result->fetch_assoc();
    $stmt->close();
    return $userInfo;
}

// ดึงข้อมูลผู้ใช้และยอดเครดิตรวม
$userInfo = getUserInfo($user_id);
$userName = $userInfo['user_name'];
$totalCredit = $userInfo['total_credit'];

// ฟังก์ชันสำหรับดึงข้อมูลรายการสินค้าใน Catalog ของผู้ใช้
function getUserCatalogItems($user_id) {
    global $conn;
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

// ดึงข้อมูลรายการสินค้า
$catalogItems = getUserCatalogItems($user_id);

// สร้าง TCPDF object
$pdf = new TCPDF();

// กำหนดข้อมูลเกี่ยวกับเอกสาร PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ระบบรายงาน');
$pdf->SetTitle('รายการของรางวัลที่สุ่มได้');
$pdf->SetSubject('รายงาน');

// เพิ่มหน้าใหม่
$pdf->AddPage();

// กำหนดฟอนต์ (เช่น TH Sarabun New)
$pdf->SetFont('THSarabunNew', '', 14);


// เพิ่มชื่อผู้ใช้และเครดิตใน PDF

$html = '<h2>ชื่อผู้ใช้: ' . htmlspecialchars($userName) . '</h2>';
$html .= '<p>ยอดเครดิตคงเหลือ: ' . htmlspecialchars($totalCredit) . ' บาท</p>';
$html .= '<h1>รายการของรางวัลที่สุ่มได้</h1>';
$html .= '<table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ชื่อสินค้า</th>
                    <th>รูปภาพสินค้า</th>
                </tr>
            </thead>
            <tbody>';

// เพิ่มข้อมูลรายการใน PDF
if (count($catalogItems) > 0) {
    foreach ($catalogItems as $item) {
        // ดึงชื่อสินค้า
        $html .= '<tr>
                    <td>' . htmlspecialchars($item['pro_name']) . '</td>
                    <td>';

        // ดึงรูปภาพสินค้าและแปลงเป็น Base64
        $imagePath = './uploads/' . htmlspecialchars($item['pro_photo']);
        if (file_exists($imagePath)) {
            // ตรวจสอบประเภท MIME ของไฟล์รูปภาพ
            $mimeType = mime_content_type($imagePath);

            // เข้ารหัสรูปภาพเป็น Base64
            $imageData = base64_encode(file_get_contents($imagePath));

            // สร้าง Data URL ตามประเภท MIME ที่ตรวจพบ
            $src = 'data:' . $mimeType . ';base64,' . $imageData;

            // ใส่รูปภาพใน HTML
            $html .= '<img src="' . $src . '" width="60" height="60">';
        } else {
            $html .= 'ไม่มีรูปภาพ';
        }

        $html .= '</td></tr>';
    }
} else {
    $html .= '<tr><td colspan="2">ไม่มีรายการของรางวัลที่สุ่มได้</td></tr>';
}

$html .= '</tbody></table>';

// แสดงเนื้อหาใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// ส่งไฟล์ PDF ให้ผู้ใช้ดาวน์โหลด
$pdf->Output('catalog_items.pdf', 'D');
?>
