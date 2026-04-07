<?php
require_once __DIR__ . '/src/config/configuracionInicial.php';
$db = getDbConnection();
$stmt = $db->query("DESCRIBE notificaciones");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
