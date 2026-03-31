<?php
require_once __DIR__ . '/src/config/configuracionInicial.php';

try {
    $db = getDbConnection();
    if (!$db) {
        die("No connection");
    }
    
    // Check if there are any capacitaciones
    $count = $db->query("SELECT COUNT(*) FROM capacitacion")->fetchColumn();
    echo "Total capacitaciones before: $count\n";
    
    if ($count == 0) {
        echo "Adding sample capacitacion...\n";
        $sql = "INSERT INTO capacitacion (nombre_capacitacion, descripcion, fecha_inicio, fecha_fin, costo) 
                VALUES ('Curso de Desarrollo Web', 'Aprende PHP y MySQL de cero a experto.', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 99.99)";
        $db->exec($sql);
        echo "Sample capacitacion added.\n";
    }
    
    $count = $db->query("SELECT COUNT(*) FROM capacitacion")->fetchColumn();
    echo "Total capacitaciones after: $count\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
