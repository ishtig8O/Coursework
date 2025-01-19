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
    echo json_encode(['error' => 'Ошибка подключения: ' . $e->getMessage()]);
    exit;
}

$latitude = isset($_GET['latitude']) ? (float)$_GET['latitude'] : null;
$longitude = isset($_GET['longitude']) ? (float)$_GET['longitude'] : null;
$month = isset($_GET['month']) ? $_GET['month'] : null;

if ($latitude === null || $longitude === null || !$month) {
    echo json_encode(['error' => 'Координаты или месяц не переданы']);
    exit;
}

// Фильтрация по координатам и месяцу
$queryWithFire = "
    SELECT SUM(DATEDIFF(CAST(STR_TO_DATE(date_end, '%d.%m.%Y') AS DATE), CAST(STR_TO_DATE(date_beginning, '%d.%m.%Y') AS DATE)) + 1) AS days_with_fire
    FROM fires_v
    WHERE ROUND(latitude, 4) = ROUND(:latitude, 4) 
      AND ROUND(longitude, 4) = ROUND(:longitude, 4)
      AND cast(DATE_FORMAT(CAST(STR_TO_DATE(date_beginning, '%d.%m.%Y') AS DATE), '%m') as unsigned) = :month
";

$queryTotalDays = "
SELECT count(distinct year)*30 AS total_days
FROM fires_v
";

try {
    $stmtWithFire = $pdo->prepare($queryWithFire);
    $stmtWithFire->execute(['latitude' => $latitude, 'longitude' => $longitude, 'month' => $month]);
    $daysWithFire = $stmtWithFire->fetchColumn();
    if (!$daysWithFire) {
        $daysWithFire = 0;
    }

    $stmtTotalDays = $pdo->prepare($queryTotalDays);
    $stmtTotalDays->execute();
    $totalDays = $stmtTotalDays->fetchColumn();

    $daysWithoutFire = $totalDays - $daysWithFire;

    $response = [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'month' => $month,
        'total_days' => $totalDays,
        'days_with_fire' => $daysWithFire,
        'days_without_fire' => $daysWithoutFire
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка выполнения запроса: ' . $e->getMessage()]);
    exit;
}
?>