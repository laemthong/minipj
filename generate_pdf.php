<?php
// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// สร้าง TCPDF object
$pdf = new TCPDF();

// กำหนดข้อมูลเกี่ยวกับเอกสาร PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('ระบบรายงาน');
$pdf->SetTitle('รายงานข้อมูลระบบ');
$pdf->SetSubject('รายงาน');

// กำหนดขอบกระดาษ
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// เพิ่มหน้าใหม่
$pdf->AddPage();

// กำหนดฟอนต์ TH Sarabun New
$pdf->SetFont('THSarabunNew', '', 14);

// ดึงข้อมูลจากฐานข้อมูล
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

// เริ่มการเขียนในเอกสาร PDF
$html = '<h1>รายงานข้อมูลระบบ</h1>';
$html .= '<p>จำนวนผู้ใช้ทั้งหมด: ' . $total_users . ' คน</p>';
$html .= '<p>จำนวนสินค้าทั้งหมด: ' . $total_products . ' ชิ้น</p>';

$html .= '<h3>ข้อมูลอัตราการสุ่ม</h3>';
$html .= '<table border="1" cellpadding="5">';
$html .= '<tr>
            <th>รหัสเรท</th>
            <th>ชื่อสินค้า</th>
            <th>รายละเอียด (%)</th>
          </tr>';

// แสดงข้อมูลอัตราการสุ่ม
while($row_rates = mysqli_fetch_assoc($result_rates)) {
    if ($totalRate > 0) {
        $percentage = ($row_rates['rate'] / $totalRate) * 100;
    } else {
        $percentage = 0;
    }

    $html .= '<tr>
                <td>' . $row_rates['rate_id'] . '</td>
                <td>' . htmlspecialchars($row_rates['pro_name']) . '</td>
                <td>' . number_format($percentage, 2) . '%</td>
              </tr>';
}

$html .= '</table>';

// ดึงข้อมูลการเติมเงินจากตาราง money
$query_deposits = "SELECT user_id, mon_price FROM money";
$result_deposits = mysqli_query($conn, $query_deposits);

// เตรียมข้อมูลสำหรับกราฟการเติมเงิน
$deposit_data = [];
while ($row_deposits = mysqli_fetch_assoc($result_deposits)) {
    $deposit_data[] = [$row_deposits['user_id'], $row_deposits['mon_price']];
}

// แสดงเนื้อหาใน PDF
$pdf->writeHTML($html, true, false, true, false, '');

// สร้างกราฟข้อมูลการเติมเงิน
if (!empty($deposit_data)) {
    $pdf->Ln();
    $pdf->SetFont('THSarabunNew', '', 12);
    $pdf->Cell(0, 10, 'กราฟข้อมูลการเติมเงิน', 0, 1, 'L');

    $maxValue = max(array_column($deposit_data, 1));
    foreach ($deposit_data as $deposit) {
        $userId = $deposit[0];
        $amount = $deposit[1];
        $barLength = ($amount / $maxValue) * 100; // คำนวณความยาวของแท่ง

        $pdf->Cell(20, 10, "User: $userId", 0, 0);
        $pdf->Cell($barLength, 10, '', 1, 1, 'L', true); // สร้างแท่ง
    }
}

// Query เพื่อดึงสินค้าที่ไม่ได้แสดงในอัตราการสุ่ม
$query_not_in_sampling = "
    SELECT p.pro_name FROM product p 
    WHERE p.pro_id NOT IN (SELECT sr.pro_id FROM sampling_rate sr)";
$result_not_in_sampling = mysqli_query($conn, $query_not_in_sampling);

// เตรียมข้อมูลสินค้าที่ไม่ได้แสดงในอัตราการสุ่ม
$products_not_in_sampling = [];
while ($row_not_in_sampling = mysqli_fetch_assoc($result_not_in_sampling)) {
    $products_not_in_sampling[] = $row_not_in_sampling['pro_name'];
}

// ตรวจสอบข้อมูลสินค้าที่ไม่ได้อยู่ในอัตราการสุ่ม
if (!empty($products_not_in_sampling)) {
    $pdf->Ln();
    $pdf->SetFont('THSarabunNew', '', 12);
    $pdf->Cell(0, 10, 'สินค้าที่ไม่ได้แสดงในอัตราการสุ่ม', 0, 1, 'L');

    foreach ($products_not_in_sampling as $product_name) {
        $pdf->Cell(0, 10, $product_name, 0, 1, 'L');
    }
}

// ส่งไฟล์ PDF ให้ผู้ใช้ดาวน์โหลด
$pdf->Output('report.pdf', 'D');
?>
