<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/provinces.php';
require_once __DIR__ . '/includes/functions.php';

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'customer';

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    echo "1. Query users OK\n";
    
    // Order stats
    $stmtStats = $conn->prepare("
        SELECT 
            COUNT(*) AS total_orders,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_orders,
            SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) AS total_spent,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_orders
        FROM orders WHERE user_id = ?
    ");
    $stmtStats->execute([$_SESSION['user_id']]);
    $stats = $stmtStats->fetch();
    echo "2. Query orders stats OK\n";
    
    // Recent orders
    $stmtRecent = $conn->prepare("
        SELECT o.*, r.name AS restaurant_name, r.image AS restaurant_image
        FROM orders o JOIN restaurants r ON o.restaurant_id = r.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC LIMIT 5
    ");
    $stmtRecent->execute([$_SESSION['user_id']]);
    $recentOrders = $stmtRecent->fetchAll();
    echo "3. Query recent orders OK\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
