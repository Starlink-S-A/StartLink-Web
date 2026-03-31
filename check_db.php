<?php
require_once __DIR__ . '/src/config/configuracionInicial.php';

try {
    $db = getDbConnection();
    if (!$db) {
        die("No connection");
    }
    
    echo "--- TABLE capacitacion ---\n";
    $stmt = $db->query("DESCRIBE capacitacion");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\n--- TABLE empresa ---\n";
    $stmt = $db->query("DESCRIBE empresa");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
