<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
include 'connectDB.php';


// Проверяем находится ли параметр action в запросе
if (isset($_GET['action']) && $_GET['action'] === 'getYears') {
    try {
        // Получаем уникальные года из базы данных
        $query = "SELECT DISTINCT year FROM fires_v ORDER BY year ASC";
        $years = $pdo->query($query)->fetchAll(PDO::FETCH_COLUMN);

        header('Content-Type: application/json');
        echo json_encode($years);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка получения годов: ' . $e->getMessage()]);
    }
    exit;
}


$year = isset($_GET['year']) ? intval($_GET['year']) : null;

try {
    if ($year) {
        // Если год указан добавляем фильтр
        $query = "SELECT latitude, longitude FROM fires_v WHERE year = :year and landmark_distance <= 1 GROUP BY latitude, longitude";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        // Если год не указан возвращаем все данные
        $query = "SELECT latitude, longitude FROM fires_v GROUP BY latitude, longitude HAVING COUNT(*) > 3";
        $stmt = $pdo->query($query);
    }

    $fires = $stmt->fetchAll();

    header('Content-Type: application/json');
    echo json_encode($fires);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Ошибка выполнения запроса: ' . $e->getMessage()]);
}
?>






