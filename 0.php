<?php
include 'config.php';
include 'navbar.php';

// ฟังก์ชันสำหรับอัปเดตเรทการสุ่ม
function updateSamplingRate($productId, $newRate) {
    global $conn;
    $sql = "UPDATE sampling_rate SET rate = ? WHERE pro_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $newRate, $productId);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// ฟังก์ชันสำหรับโหลดข้อมูลสินค้าและเรทการสุ่มใหม่
function reloadProducts() {
    global $conn, $products;
    $products = [];
    $sql = "SELECT p.pro_id, p.pro_name, p.pro_photo, sr.rate FROM product p JOIN sampling_rate sr ON p.pro_id = sr.pro_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                'id' => $row['pro_id'],
                'name' => $row['pro_name'],
                'image' => $row['pro_photo'],
                'rate' => floatval($row['rate'])
            ];
        }
    }
}

// ฟังก์ชันสำหรับแทรกข้อมูลผลการสุ่มลงในตาราง catalog
function insertIntoCatalog($userId, $productId) {
    global $conn;
    $sql = "INSERT INTO catalog (user_id, pro_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $productId);  // user_id และ pro_id เป็นประเภทข้อมูล integer
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// API endpoint สำหรับอัปเดตเรทการสุ่ม
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateRate') {
    $productId = $_POST['productId'];
    $newRate = $_POST['newRate'];
    $result = updateSamplingRate($productId, $newRate);
    if ($result) {
        reloadProducts(); // โหลดข้อมูลใหม่หลังจากอัปเดต
    }
    echo json_encode(['success' => $result]);
    exit;
}

// API endpoint สำหรับแทรกสินค้าที่ชนะลงในตาราง catalog
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insertIntoCatalog') {
    $userId = $_POST['userId'];
    $productId = $_POST['productId'];
    $result = insertIntoCatalog($userId, $productId);

    echo json_encode(['success' => $result]);
    exit;
}

// โหลดข้อมูลสินค้าเริ่มต้น
reloadProducts();

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สุ่มของรางวัล - Termtang Game Online Store Center</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body,
        html {
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
            margin-top: 80px;
            margin-bottom: -85px;
        }

        /* Slot Machine Section */
        .slot-machine {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: calc(100vh - 100px);
            padding: 20px;
            position: relative;
        }

        .reel-container {
            width: 990px;
            height: 330px;
            display: flex;
            justify-content: space-between;
            background-color: #000;
            border: 15px solid gold;
            border-radius: 30px;
            overflow: hidden;
            margin-bottom: 60px;
        }

        .reel {
            width: 300px;
            height: 900px;
            overflow: hidden;
        }

        .reel img {
            width: 300px;
            height: 300px;
            object-fit: cover;
        }

        .spin-btn {
            background-color: #FFD700;
            color: #4A148C;
            padding: 25px 50px;
            font-size: 30px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: -35px;
        }

        .spin-btn:hover {
            background-color: #FFA500;
            transform: scale(1.05);
        }

        .spin-btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        @keyframes spin {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(-100%);
            }
        }

        .reel.running {
            animation: spin 0.2s linear infinite;
        }

        .percentage-display {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
        }

        .percentage-item {
            margin-bottom: 5px;
        }

        .percentage-title {
            position: absolute;
            left: 20px;
            top: 35%;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="page-title">
        สุ่มรางวัลลุ้นรับโชค
    </div>

    <div class="slot-machine">
        <div class="percentage-title">โอกาสการได้รางวัล</div>
        <div class="percentage-display" id="percentageDisplay"></div>

        <div class="reel-container">
            <div class="reel" id="reel1">
                <?php foreach ($products as $product): ?>
                    <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <?php endforeach; ?>
            </div>
            <div class="reel" id="reel2">
                <?php foreach ($products as $product): ?>
                    <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <?php endforeach; ?>
            </div>
            <div class="reel" id="reel3">
                <?php foreach ($products as $product): ?>
                    <img src="./uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                <?php endforeach; ?>
            </div>
        </div>
        <button class="spin-btn" onclick="spinReels()">สุ่มรางวัล</button>
    </div>

    <script>
        let products = <?php echo json_encode($products); ?>;
        let isSpinning = false;

        function calculatePercentages() {
            const totalRate = products.reduce((sum, product) => sum + parseFloat(product.rate), 0);
            return products.map(product => ({
                name: product.name,
                percentage: ((product.rate / totalRate) * 100).toFixed(2)
            }));
        }

        function displayPercentages() {
            const percentages = calculatePercentages();
            const displayElement = document.getElementById('percentageDisplay');
            displayElement.innerHTML = percentages.map(item =>
                `<div class="percentage-item">${item.name}: ${item.percentage}%</div>`
            ).join('');
        }

        function spinReels() {
            if (isSpinning) return;
            isSpinning = true;

            const reels = document.querySelectorAll('.reel');
            reels.forEach(reel => reel.classList.add('running'));

            // หยุดรีลทีละอันโดยมีการหน่วงเวลา
            setTimeout(() => stopReel(0), 2000);
            setTimeout(() => stopReel(1), 3000);
            setTimeout(() => stopReel(2), 4000);
        }

        function stopReel(reelIndex) {
            const reel = document.querySelectorAll('.reel')[reelIndex];
            reel.classList.remove('running');

            const randomProduct = getRandomProduct();
            reel.innerHTML = `<img src="./uploads/${randomProduct.image}" alt="${randomProduct.name}">`;

            if (reelIndex === 2) {
                isSpinning = false;
                checkWin();
            }
        }

        function getRandomProduct() {
            const totalRate = products.reduce((sum, product) => sum + parseFloat(product.rate), 0);
            let random = Math.random() * totalRate;

            for (const product of products) {
                if (random < product.rate) {
                    return product;
                }
                random -= product.rate;
            }

            return products[products.length - 1];
        }

        function checkWin() {
            const reels = document.querySelectorAll('.reel');
            const results = Array.from(reels).map(reel => {
                const img = reel.querySelector('img');
                return products.find(p => p.image === img.src.split('/').pop());
            });

            if (results[0].id === results[1].id && results[1].id === results[2].id) {
                Swal.fire({
                    title: 'ยินดีด้วย!',
                    html: `คุณได้รับรางวัล: ${results[0].name}<br>
                           <img src="./uploads/${results[0].image}" alt="${results[0].name}" style="width: 200px; height: 200px;">`,
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    const userId = 1; // คุณสามารถเปลี่ยน user_id ตรงนี้ตามจริงได้
                    const productId = results[0].id;

                    // ทำการ POST เพื่อแทรกข้อมูล
                    fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=insertIntoCatalog&userId=${userId}&productId=${productId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('แทรกสินค้าที่ชนะลงใน catalog เรียบร้อยแล้ว');
                        } else {
                            console.error('ไม่สามารถแทรกสินค้าที่ชนะลงใน catalog ได้');
                        }
                    });

                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'เสียใจด้วย',
                    text: 'คุณไม่ได้รับรางวัลในครั้งนี้ ลองใหม่อีกครั้งนะ!',
                    icon: 'error',
                    confirmButtonText: 'ลองอีกครั้ง'
                }).then(() => {
                    window.location.reload();
                });
            }
        }

        window.onload = displayPercentages;
    </script>
</body>

</html>
