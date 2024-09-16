<?php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['userId'];
    $proId = $_POST['proId'];

    if (savePrizeToCatalog($userId, $proId)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}

function savePrizeToCatalog($userId, $proId) {
    global $conn;
    
    $sql = "INSERT INTO catalog (user_id, pro_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $userId, $proId);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
?>
