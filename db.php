<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
// Подключение к базе данных php -S localhost:5007    
$host = 'localhost:8080';
$db   = 'courseworkFires';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo "Ошибка подключения: " . $e->getMessage();
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}


$queryFireCount = "SELECT year, COUNT(*) as count FROM fires_v GROUP BY year ORDER BY year";
$queryAreaAffected = "SELECT year, SUM(area_total) as total_area FROM fires_v GROUP BY year ORDER BY year";

$fireCount = $pdo->query($queryFireCount)->fetchAll();
$areaAffected = $pdo->query($queryAreaAffected)->fetchAll();

$response = [
    'yearlyFireCount' => $fireCount,
    'yearlyAreaAffected' => $areaAffected
];

header('Content-Type: application/json');
echo json_encode($response);
?>
