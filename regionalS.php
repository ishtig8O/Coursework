<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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
    exit;
}

// Запрос для регионов с наибольшей плотностью пожаров
$queryHighRiskRegions = "
    SELECT region, COUNT(*) AS fire_count, SUM(area_total) AS total_area
    FROM fires_v
    GROUP BY region
    ORDER BY fire_count DESC
    LIMIT 10
";

// Запрос для региональной динамики по годам
$queryRegionalTrends = "
    SELECT region, year, COUNT(*) AS fire_count, SUM(area_total) AS total_area
    FROM fires_v
    where region IN 
    (
    select region from 
    (
    SELECT region from fires_v group by region order by count(*) DESC limit 10    
    ) as topregions
    )   
    GROUP BY region, year
    ORDER BY region, year
";

// Запрос для лидеров и аутсайдеров
$queryLeadersAndOutsiders = "
    SELECT region, COUNT(*) AS fire_count, SUM(area_total) AS total_area
    FROM fires_v
    GROUP BY region
    ORDER BY total_area DESC
";

try {
    $highRiskRegions = $pdo->query($queryHighRiskRegions)->fetchAll();
    $regionalTrends = $pdo->query($queryRegionalTrends)->fetchAll();
    $leadersAndOutsiders = $pdo->query($queryLeadersAndOutsiders)->fetchAll();
} catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
    exit;
}

$response = [
    'highRiskRegions' => $highRiskRegions,
    'regionalTrends' => $regionalTrends,
    'leadersAndOutsiders' => $leadersAndOutsiders
];

header('Content-Type: application/json');
echo json_encode($response);
?>