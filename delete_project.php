<?php
session_start();

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

// Получаем данные из POST запроса
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !is_numeric($data['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$project_id = $data['id'];

try {
    // Удаляем проект, принадлежащий текущему пользователю
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$project_id, $user_id]);
    
    if ($stmt->rowCount() > 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Проект не найден или не принадлежит вам']);
    }
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
