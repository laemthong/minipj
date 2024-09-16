<?php
include 'config.php';

if (isset($_GET['id'])) {
    $rate_id = $_GET['id'];

    // ดึงข้อมูลของ rate ที่จะทำการแก้ไข
    $sql = "SELECT rate, pro_id FROM sampling_rate WHERE rate_id = $rate_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rate = $row['rate'];
        $pro_id = $row['pro_id'];
    } else {
        echo "ไม่พบข้อมูล!";
        exit;
    }
} else {
    echo "ไม่พบ ID!";
    exit;
}

// อัพเดตข้อมูลในฐานข้อมูลเมื่อผู้ใช้ส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_rate = $_POST['rate'];

    $sql = "UPDATE sampling_rate SET rate = '$new_rate' WHERE rate_id = $rate_id";

    if ($conn->query($sql) === TRUE) {
        // หลังจากบันทึกข้อมูลสำเร็จให้รีเฟรชและกลับไปหน้าข้อมูลอัตราการสุ่ม
        header("Location: sampling_rate.php"); 
        exit;
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขอัตราการสุ่ม</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px 0px #000;
            max-width: 400px;
            margin: auto;
        }
        input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>แก้ไขอัตราการสุ่ม</h1>
        <form method="POST">
            <label for="rate">Rate:</label>
            <input type="text" id="rate" name="rate" value="<?php echo $rate; ?>" required>
            <input type="submit" value="บันทึกการแก้ไข">
        </form>
    </div>
</body>
</html>
