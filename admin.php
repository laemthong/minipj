<?php
session_start();
include 'config.php';

$login_error = false; // กำหนดค่าเริ่มต้นของ $login_error เป็น false

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // คำสั่ง SQL ในการดึงข้อมูลจากฐานข้อมูลสำหรับ admin
    $sql = "SELECT * FROM admin WHERE admin_name='$username' AND admin_password='$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // ถ้าพบข้อมูลผู้ใช้
        $_SESSION['username'] = $username;
        header("Location: Sampling_rate.php"); // ย้ายไปที่หน้า Samling_rate.php
        exit();
    } else {
        $login_error = true; // กำหนดให้มีข้อผิดพลาด
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        /* สไตล์ของคุณ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, "Lato", sans-serif;
            background-color: #efefef;
        }

        .container {
            width: 280px;
            height: 450px;
            background: rgb(255, 255, 255);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 5px;
            box-shadow: 15px 10px 15px;
        }

        #user-men {
            font-size: 2.5rem;
            margin: 10px 45%;
            color: rgb(58, 58, 161);
        }

        .welcome-heading {
            font-size: 1.1rem;
            text-align: center;
            text-transform: capitalize;
            color: rgb(91, 129, 241);
        }

        .input-container {
            margin-top: 20px;
        }

        .input-box {
            width: 220px;
            height: 30px;
            margin: 10px 30px;
            border-radius: 40px;
            outline: none;
            border: none;
            box-shadow: 10px 10px 13px;
            padding-left: 10px;
        }

        .input-box1 {
            width: 220px;
            height: 30px;
            margin: 10px 30px 30px;
            border-radius: 40px;
            outline: none;
            border: none;
            box-shadow: 10px 10px 13px;
            padding-left: 10px;
        }

        .login-btn {
            display: block;
            width: 220px;
            height: 40px;
            margin: 10px auto;
            border-radius: 40px;
            text-align: center;
            line-height: 40px;
            text-decoration: none;
            color: #ffffff;
            text-transform: capitalize;
            font-size: 16px;
            cursor: pointer;
            border: none;
            background-image: linear-gradient(to left, #fa8072, #ff4500);
        }

        .forget-password {
            position: absolute;
            margin: 20px 70px;
            text-decoration: none;
            font-weight: bold;
            color: #555;
        }

    </style>
</head>
<body>
    <div class="container">
        <i id="user-men" class="fa fa-user-plus" aria-hidden="true"></i>
        <h2 class="welcome-heading">Admin Login</h2>
        <form method="post" action="">
            <div class="input-container">
                <input class="input-box" type="text" name="username" placeholder="Username" required>
                <input class="input-box1" type="password" name="password" placeholder="Password" required>
                <button class="login-btn" type="submit">Login</button>
            </div>
        </form>
    </div>

    <!-- ตรวจสอบและแสดงป๊อปอัปถ้ามีข้อผิดพลาดในการเข้าสู่ระบบ -->
    <?php if ($login_error): ?>
    <script>
        alert("Username หรือ Password ผิด!");
    </script>
    <?php endif; ?>
</body>
</html>
