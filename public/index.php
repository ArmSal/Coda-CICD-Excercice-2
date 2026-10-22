<?php

declare(strict_types=1);

use App\Api;
use App\Database;
use App\TaskRepository;
use App\TaskValidator;

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/health') {
    echo json_encode(['status' => 'ok']);
    exit;
}

try {
    $body = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
    $api = new Api(new TaskRepository(Database::connect()), new TaskValidator());
    [$status, $data] = $api->handle($method, $path, $body);
} catch (PDOException $e) {
    error_log($e->getMessage());
    [$status, $data] = [500, ['error' => 'Base de données indisponible']];
}

http_response_code($status);
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
