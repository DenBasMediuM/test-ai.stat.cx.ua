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

if (!$data || !isset($data['name']) || !isset($data['content'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Неверные данные']);
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$name = $data['name'];
$content = $data['content'];
$question = $data['question'] ?? '';

try {
    // Проверяем, существует ли проект с таким именем у этого пользователя
    $stmt = $pdo->prepare("SELECT id FROM projects WHERE user_id = ? AND name = ?");
    $stmt->execute([$user_id, $name]);
    
    if ($stmt->rowCount() > 0) {
        // Обновляем существующий проект
        $project = $stmt->fetch();
        $stmt = $pdo->prepare("UPDATE projects SET content = ?, question = ? WHERE id = ?");
        $stmt->execute([$content, $question, $project['id']]);
    } else {
        // Создаем новый проект
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, name, content, question) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $content, $question]);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
