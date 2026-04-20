<?php
require_once __DIR__ . '/../config/database.php';

try {
    $sql = file_get_contents('upgrade_v2.sql');
    if ($sql === false) {
        die("Could not read upgrade_v2.sql");
    }

    // Split SQL into meaningful statements if needed, but PDO execute handles multiple statements if ATTR_EMULATE_PREPARES is true.
    // However, we set ATTR_EMULATE_PREPARES to false in db_config.php. So we might need to change it temporarily or split by ';'
    
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    echo "Migration completed successfully!";
} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage();
}
