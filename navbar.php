<?php
include 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}



?>

<style>
   /* Common Font Style */
.upper-navbar, .main-navbar {
    font-family: Arial, sans-serif;
}

/* Upper Navbar */
.upper-navbar {
    background-color: #333;
    color: white;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.upper-navbar .left {
    font-size: 14px;
}

.upper-navbar .right {
    font-size: 14px;
}

.upper-navbar a {
    color: white;
    text-decoration: none;
    margin-left: 15px;
}

/* Main Navbar */
.main-navbar {
    background-color: white;
    padding: 10px 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    display: flex;
    justify-content: center;
    position: sticky;
    top: 0;
    z-index: 999;
}

.main-navbar a {
    margin: 0 15px;
    text-decoration: none;
    color: #333;
    font-size: 16px;
    position: relative;
}

.main-navbar a:hover {
    color: #007BFF;
}

/* Dropdown Menu */
.main-navbar .dropdown {
    position: relative;
    display: inline-block;
}

.main-navbar .dropdown-content {
    display: none;
    position: absolute;
    background-color: white;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    min-width: 200px;
}

.main-navbar .dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.main-navbar .dropdown-content a:hover {
    background-color: #f1f1f1;
}

.main-navbar .dropdown:hover .dropdown-content {
    display: block;
}

/* Active Link */
.main-navbar a.active {
    color: #007BFF;
}
</style>

<!-- Upper Navbar -->
<div class="upper-navbar">
    <div class="left">
        <a href="https://www.facebook.com/nicky.topteen.52" target="_blank"><i class="fab fa-facebook"></i> Thananan Seeduang</a>
    </div>
    <div class="right">
        <?php if (isset($_SESSION['username'])): ?>
           
            <a href="#"><i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?></a> |
            <a href="logout.php" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?');"><i class="fas fa-sign-out-alt"></i> ออกจากระบบ</a>
        <?php else: ?>
            <a href="login.php"><i class="fas fa-lock"></i> เข้าสู่ระบบ</a> |
            <a href="register.php"><i class="fas fa-user-plus"></i> สมัครสมาชิก</a>
        <?php endif; ?>
    </div>
</div>



<!-- Main Navbar -->
<div class="main-navbar">
    <a href="index.php" <?php echo ($_SERVER['PHP_SELF'] == '/index.php') ? 'class="active"' : ''; ?>>หน้าหลัก</a>
    <a href="random.php" <?php echo ($_SERVER['PHP_SELF'] == '/random.php') ? 'class="active"' : ''; ?>>สุ่มของรางวัล</a>
    <a href="catalog.php" <?php echo ($_SERVER['PHP_SELF'] == '/catalog.php') ? 'class="active"' : ''; ?>>รางวัลที่สุ่มได้</a>
    <a href="money.php" <?php echo ($_SERVER['PHP_SELF'] == '/money.php') ? 'class="active"' : ''; ?>>เติมเครดิต</a>
</div>


