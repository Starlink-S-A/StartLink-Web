<?php
require_once __DIR__ . '/src/config/configuracionInicial.php';

try {
    $db = getDbConnection();
    if (!$db) {
        die("No connection");
    }
    
    echo "Adding id_empresa column to capacitacion table...\n";
    $sql = "ALTER TABLE capacitacion ADD COLUMN id_empresa INT NULL AFTER creador_id";
    $db->exec($sql);
    
    echo "Adding foreign key constraint...\n";
    $sql = "ALTER TABLE capacitacion ADD CONSTRAINT fk_capacitacion_empresa FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE SET NULL";
    $db->exec($sql);
    
    echo "Migration completed successfully.\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
