<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
include 'connectDB.php';


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
