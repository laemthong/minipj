<?php
session_start();
include 'config.php';

$register_error = false; // กำหนดค่าเริ่มต้นของ $register_error เป็น false
$register_success = false; // กำหนดค่าเริ่มต้นของ $register_success เป็น false

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        // ตรวจสอบว่าชื่อผู้ใช้ซ้ำหรือไม่
        $sql = "SELECT * FROM user WHERE user_name='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows == 0) {
            // ถ้าไม่มีชื่อผู้ใช้นี้ในระบบ ให้เพิ่มผู้ใช้ใหม่
            $sql_insert = "INSERT INTO user (user_name, user_pass) VALUES ('$username', '$password')";
            if ($conn->query($sql_insert) === TRUE) {
                $register_success = true; // ลงทะเบียนสำเร็จ
            } else {
                $register_error = true; // เกิดข้อผิดพลาดในการบันทึกข้อมูล
            }
        } else {
            $register_error = true; // ชื่อผู้ใช้ซ้ำ
        }
    } else {
        $register_error = true; // รหัสผ่านไม่ตรงกัน
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            height: 500px;
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

        .register-btn {
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
            background-image: linear-gradient(to left, rgb(170, 170, 241), rgb(39, 39, 250));
        }

        .login-link {
            position: absolute;
            margin-top: 40px;
            font-size: 0.8rem;
            padding: 10px 20px;
            color: #555;
        }

        .login-now {
            text-decoration: none;
            margin-left: 10px;
            font-size: 0.8rem;
            font-weight: bold;
            color: #555;
        }

    </style>
</head>
<body>
    <div class="container">
        <i id="user-men" class="fa fa-user-plus" aria-hidden="true"></i>
        <h2 class="welcome-heading">Welcome to <span class="dkp-heading">Register</span></h2>
        <form method="post" action="">
            <div class="input-container">
                <input class="input-box" type="text" name="username" placeholder="Username" required>
                <input class="input-box1" type="password" name="password" placeholder="Password" required>
                <input class="input-box1" type="password" name="confirm_password" placeholder="Confirm Password" required>
                <button class="register-btn" type="submit">Register</button>
            </div>
        </form>
        <div class="login-link">Already have an account? 
            <a href="login.php" class="login-now">Login Now</a>
        </div>
    </div>

    <!-- ตรวจสอบและแสดงป๊อปอัปถ้ามีข้อผิดพลาดในการลงทะเบียน -->
    <?php if ($register_error): ?>
    <script>
        alert("Error in registration. Please try again.");
    </script>
    <?php elseif ($register_success): ?>
    <script>
        alert("Registration successful! You can now login.");
        window.location.href = 'login.php';
    </script>
    <?php endif; ?>
</body>
</html>
