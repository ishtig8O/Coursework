<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
include 'connectDB.php';


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
