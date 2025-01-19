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

// Запрос для количества пожаров по месяцам
$queryMonthlyFireData = "
SELECT 
CAST(SUBSTRING(date_beginning, 4, 2) AS UNSIGNED) AS month, 
COUNT(*) AS count
FROM fires_v
GROUP BY month
ORDER BY month;
";

// Запрос для средней площади пожаров по месяцам
$queryMonthlyAreaData = "
SELECT 
CAST(SUBSTRING(date_beginning, 4, 2) AS UNSIGNED) AS month,
AVG(area_beginning) AS average_area
FROM fires_v
WHERE date_beginning REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$' -- Проверка на корректный формат
GROUP BY month
ORDER BY month;
";

try {
    $monthlyFireData = $pdo->query($queryMonthlyFireData)->fetchAll();
    $monthlyAreaData = $pdo->query($queryMonthlyAreaData)->fetchAll();
} catch (PDOException $e) {
    echo "Ошибка выполнения запроса: " . $e->getMessage();
    exit;
}

$response = [
    'monthlyFireData' => $monthlyFireData,
    'monthlyAreaData' => $monthlyAreaData
];

header('Content-Type: application/json');
echo json_encode($response);
?>
