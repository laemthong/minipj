<?php
include 'config.php';

session_start();

function getCurrentCredit($user_id) {
    global $conn;
    $sql = "SELECT SUM(mon_price) as total_credit FROM money WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['total_credit'] ?? 0;
    }
    return 0;
}

if (isset($_SESSION['user_id'])) {
    $credit = getCurrentCredit($_SESSION['user_id']);
    echo json_encode(['credit' => $credit]);
} else {
    echo json_encode(['credit' => 0]);
}
?>