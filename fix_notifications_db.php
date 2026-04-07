<?php
require_once __DIR__ . '/src/config/configuracionInicial.php';
try {
    $db = getDbConnection();
    // Aumentamos el tamaño de la columna tipo para evitar truncamientos
    $sql = "ALTER TABLE notificaciones MODIFY COLUMN tipo VARCHAR(50)";
    $db->exec($sql);
    echo "Columna 'tipo' actualizada correctamente a VARCHAR(50).\n";
} catch (Exception $e) {
    echo "Error al actualizar la tabla: " . $e->getMessage() . "\n";
}
