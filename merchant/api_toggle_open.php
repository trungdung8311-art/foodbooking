<?php
// merchant/api_toggle_open.php
require_once '../db_config.php';
require_once 'auth_check.php';
requireMerchant();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $restaurant = getMerchantRestaurant($conn);
    if ($restaurant && isset($_POST['restaurant_id']) && $_POST['restaurant_id'] == $restaurant['id']) {
        $isOpen = isset($_POST['is_open']) ? 1 : 0;
        $stmt = $conn->prepare("UPDATE restaurants SET is_open = ? WHERE id = ?");
        $stmt->execute([$isOpen, $restaurant['id']]);
        
        // redirect back to referer
        $referer = $_SERVER['HTTP_REFERER'] ?? '/foodbooking/merchant/index.php';
        header("Location: $referer");
        exit;
    }
}

header('Location: /foodbooking/merchant/index.php');
exit;
