<?php
// database/run_all_migrations.php
// Tạo tất cả bảng còn thiếu trong DB cicafood
require_once __DIR__ . '/../config/database.php';

$results = [];

// ── 1. Bảng user_vouchers ──────────────────────────────────────
$results[] = runSQL($conn, "user_vouchers", "
    CREATE TABLE IF NOT EXISTS `user_vouchers` (
      `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `user_id`    INT UNSIGNED NOT NULL,
      `voucher_id` INT UNSIGNED NOT NULL,
      `is_used`    TINYINT(1) DEFAULT 0,
      `used_at`    TIMESTAMP NULL DEFAULT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`)    REFERENCES `users`(`id`)    ON DELETE CASCADE,
      FOREIGN KEY (`voucher_id`) REFERENCES `vouchers`(`id`) ON DELETE CASCADE,
      UNIQUE KEY `unique_user_voucher` (`user_id`, `voucher_id`),
      INDEX `idx_user_id`    (`user_id`),
      INDEX `idx_voucher_id` (`voucher_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// ── 2. Bảng favorites (nhà hàng yêu thích) ────────────────────
$results[] = runSQL($conn, "favorites", "
    CREATE TABLE IF NOT EXISTS `favorites` (
      `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      `user_id`       INT UNSIGNED NOT NULL,
      `restaurant_id` INT UNSIGNED NOT NULL,
      `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`user_id`)       REFERENCES `users`(`id`)       ON DELETE CASCADE,
      FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants`(`id`) ON DELETE CASCADE,
      UNIQUE KEY `unique_favorite` (`user_id`, `restaurant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// ── 3. Cột restaurants.province (nếu chưa có) ─────────────────
$results[] = runSQL($conn, "restaurants.province", "
    ALTER TABLE `restaurants`
    ADD COLUMN IF NOT EXISTS `province` VARCHAR(100) DEFAULT NULL
    COMMENT 'Tỉnh/thành phố' AFTER `address`
");

// ── 4. Cột vouchers.restaurant_id (nếu chưa có) ───────────────
$results[] = runSQL($conn, "vouchers.restaurant_id", "
    ALTER TABLE `vouchers`
    ADD COLUMN IF NOT EXISTS `restaurant_id` INT UNSIGNED DEFAULT NULL
    COMMENT 'NULL=toàn platform' AFTER `is_active`
");

// ── 5. Cập nhật province cho dữ liệu mẫu ─────────────────────
$results[] = runSQL($conn, "UPDATE restaurants.province", "
    UPDATE `restaurants` SET `province` = 'TP. Hồ Chí Minh' WHERE `province` IS NULL
");

// ── Hàm helper ────────────────────────────────────────────────
function runSQL(PDO $conn, string $name, string $sql): string {
    try {
        $conn->exec($sql);
        return "✅ $name";
    } catch (PDOException $e) {
        return "❌ $name: " . $e->getMessage();
    }
}

// ── Output ─────────────────────────────────────────────────────
foreach ($results as $r) echo $r . PHP_EOL;

// Xác minh các bảng
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
echo PHP_EOL . "📋 Các bảng trong DB: " . implode(', ', $tables) . PHP_EOL;
