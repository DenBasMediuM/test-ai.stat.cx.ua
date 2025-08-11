<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: projects.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$project_id = $_GET['id'];

// Получаем проект и проверяем, принадлежит ли он текущему пользователю
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $user_id]);
$project = $stmt->fetch();

if (!$project) {
    header("Location: projects.php");
    exit;
}

// Устанавливаем более высокий лимит на время выполнения скрипта для больших проектов
set_time_limit(120);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($project['name']); ?> - AI Проекты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Стили для корректного отображения изображений */
        img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 15px auto;
            border-radius: 5px;
        }
        #project-content {
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">AI Проекты</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="projects.php">Мои проекты</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Выйти (<?php echo htmlspecialchars($username); ?>)</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <a href="projects.php" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Назад к проектам
            </a>
            <h1 class="mt-2"><?php echo htmlspecialchars($project['name']); ?></h1>
            <p class="text-muted">
                Создан: <?php echo date('d.m.Y H:i', strtotime($project['created_at'])); ?>
            </p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    Результат
                </div>
                <div class="card-body">
                    <div id="project-content">
                        <?php echo $project['content']; // Выводим содержимое как есть ?>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($project['question'])): ?>
            <div class="card shadow-sm">
                <div class="card-header">
                    Запрос к ИИ
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($project['question'])); ?></p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="col-lg-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Действия</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger" id="deleteProject">
                            <i class="bi bi-trash"></i> Удалить проект
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Скрипт для корректного отображения изображений в base64
document.addEventListener('DOMContentLoaded', function() {
    // Ищем все img элементы с src, начинающимся с =data:
    const images = document.querySelectorAll('img[src^="=data:"]');
    images.forEach(img => {
        // Удаляем знак = из начала src
        img.src = img.src.replace(/^=/, '');
        console.log('Исправлен src изображения:', img.src.substring(0, 30) + '...');
    });
});

document.getElementById('deleteProject').addEventListener('click', async function() {
    if (confirm('Вы уверены, что хотите удалить этот проект?')) {
        try {
            const response = await fetch('delete_project.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: <?php echo $project_id; ?> })
            });
            
            const result = await response.json();
            
            if (result.success) {
                window.location.href = 'projects.php';
            } else {
                alert('Ошибка при удалении проекта: ' + result.error);
            }
        } catch (error) {
            alert('Произошла ошибка: ' + error.message);
        }
    }
});
</script>
</body>
</html>
