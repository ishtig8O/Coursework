
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

if ($latitude === null || $longitude === null) {
    echo json_encode(['error' => 'Координаты не переданы']);
    exit;
}

// Проверка наличия точных координат
$queryCheckExact = "
    SELECT COUNT(*) 
    FROM fires_v
    WHERE ROUND(latitude, 2) = ROUND(:latitude, 2) AND ROUND(longitude, 2) = ROUND(:longitude, 2)
";

$stmtCheckExact = $pdo->prepare($queryCheckExact);
$stmtCheckExact->execute(['latitude' => $latitude, 'longitude' => $longitude]);
$exactMatch = $stmtCheckExact->fetchColumn();

if ($exactMatch == 0) {
    // Если точных координат нет, ищем ближайшие
    $queryFindClosest = "
        SELECT latitude, longitude, 
               SQRT(POW(latitude - :latitude, 2) + POW(longitude - :longitude, 2)) AS distance
        FROM fires_v
        ORDER BY distance ASC
        LIMIT 1
    ";
    $stmtFindClosest = $pdo->prepare($queryFindClosest);
    $stmtFindClosest->execute(['latitude' => $latitude, 'longitude' => $longitude]);
    $closest = $stmtFindClosest->fetch();

    if ($closest) {
        $latitude = $closest['latitude'];
        $longitude = $closest['longitude'];
    } else {
        echo json_encode(['error' => 'Данные не найдены для указанных координат']);
        exit;
    }
}

// Запрос для подсчета дней с пожарами
$queryWithFire = "
    SELECT sum(DATEDIFF(CAST(STR_TO_DATE(date_end, '%d.%m.%Y') AS DATE), CAST(STR_TO_DATE(date_beginning, '%d.%m.%Y') AS DATE)) + 1) AS days_with_fire
    FROM fires_v
    WHERE round(latitude,2) = round(:latitude,2) AND round(longitude,2) = round(:longitude,2)
";

// Запрос для общего количества дней
$queryTotalDays = "
    SELECT count(distinct year)*365 AS total_days
    FROM fires_v
";

try {
    $stmtWithFire = $pdo->prepare($queryWithFire);
    $stmtWithFire->execute(['latitude' => $latitude, 'longitude' => $longitude]);
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
        'total_days' => $totalDays,
        'days_with_fire' => $daysWithFire,
        'days_without_fire' => $daysWithoutFire,
        'exact_match' => $exactMatch > 0
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка выполнения запроса: ' . $e->getMessage()]);
    exit;
}
?>


