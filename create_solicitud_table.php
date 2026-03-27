<?php
require_once __DIR__ . '/src/config/configuracionInicial.php';

try {
    $pdo = getDbConnection();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS solicitud_contratacion (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_candidato INT NOT NULL,
        id_empresa INT NOT NULL,
        salario_base DECIMAL(10,2) NULL,
        horas_semanales_estandar DECIMAL(5,2) NULL,
        estado ENUM('pendiente', 'aceptada', 'rechazada') DEFAULT 'pendiente',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_candidato) REFERENCES usuario(id) ON DELETE CASCADE,
        FOREIGN KEY (id_empresa) REFERENCES empresa(id_empresa) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Table 'solicitud_contratacion' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
