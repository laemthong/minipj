<?php
session_start();
include 'config.php';
include 'navbar.php';

// ตรวจสอบการล็อกอิน
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // ตรวจสอบข้อมูลผู้ใช้
    $sql = "SELECT * FROM user WHERE user_name='$username' AND user_pass='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // ถ้าพบข้อมูลผู้ใช้
        $_SESSION['username'] = $username;
        header("Location: index.php"); // กลับไปยังหน้าแรก
        exit();
    } else {
        echo "Username หรือ Password ผิด!";
    }
}

// ดึงข้อมูลรูปภาพสินค้าจากฐานข้อมูล
$product_images = [];
$sql = "SELECT pro_photo FROM product WHERE pro_photo IS NOT NULL AND pro_photo != ''";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $product_images[] = $row['pro_photo'];
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termtang Game Online Store Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #f4f4f4;
        }

       

        /* Header Section */
        .header {
            background-color: #4A148C;
            height: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        .header-content {
            max-width: 40%;
            position: relative;
            z-index: 2;
            text-align: left;
            margin-right: auto;
        }

        .slider-container {
            width: 50%;
            height: 400px;
            position: relative;
            z-index: 1;
            margin-left: auto;
            overflow: hidden;
        }

        .slider-items {
            display: flex;
            transition: transform 0.5s ease-in-out;
            height: 100%; 
        }

        /* แต่ละสไลด์ */
        .slider-item {
            min-width: 100%;
            height: 100%;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 24px;
            position: relative;
            z-index: 2;
        }

        .slider-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 10px;
        }

        /* Slider Controls */
        .slider-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 30px;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            border: none;
            cursor: pointer;
            padding: 10px;
            z-index: 3;
        }

        .slider-control.left {
            left: 10px;
        }

        .slider-control.right {
            right: 10px;
        }

        .header-content h1 {
            font-size: 48px;
            margin-bottom: 10px;
           
        }

        .header-content p {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .header-content a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
        }

        .header-content a:hover {
            background-color: #0056b3;
        }
       
        .reward-caption {
            text-align: center;
            color: white;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
    <script>
        function confirmLogout() {
            return confirm("คุณต้องการออกจากระบบหรือไม่?");
        }
    </script>
</head>
<body>



<!-- Header Section -->
<div class="header">
    <div class="header-content">
        <h1>ระบบสุ่มรางวัลใหม่</h1>
        <p>สุ่มรางวัลเริ่มต้นเพียง 10 เครดิตเท่านั้น</p>
        <a href="random.php">เล่นทันที</a>
    </div>

    <!-- Slider Section -->
    <div class="slider-container">
        <div class="slider-items">
            <?php foreach ($product_images as $image): ?>
                <div class="slider-item">
                    <img src="uploads/<?php echo $image; ?>" alt="Product Image">
                </div>
            <?php endforeach; ?>
        </div>
        <button class="slider-control left" onclick="prevSlide()"><i class="fas fa-chevron-left"></i></button>
        <button class="slider-control right" onclick="nextSlide()"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slider-item');
    const totalSlides = slides.length;

    function showSlide(index) {
        const sliderItems = document.querySelector('.slider-items');
        if (index >= totalSlides) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = totalSlides - 1;
        } else {
            currentSlide = index;
        }
        sliderItems.style.transform = 'translateX(' + (-currentSlide * 100) + '%)';
    }

    function nextSlide() {
        showSlide(currentSlide + 1);
    }

    function prevSlide() {
        showSlide(currentSlide - 1);
    }

    // เพิ่มการเลื่อนอัตโนมัติ
    setInterval(nextSlide, 2000); // เลื่อนทุกๆ 2 วินาที
</script>

</body>
</html>
