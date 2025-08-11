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

// Обработка отправки формы редактирования
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    $name = trim($_POST['project_name'] ?? '');
    $question = $_POST['question'] ?? '';
    
    if (empty($name)) {
        $message = '<div class="alert alert-danger">Название проекта не может быть пустым</div>';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE projects SET name = ?, question = ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$name, $question, $project_id, $user_id]);
            $message = '<div class="alert alert-success">Проект успешно обновлен</div>';
            
            // Обновляем данные проекта после сохранения
            $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->execute([$project_id]);
            $project = $stmt->fetch();
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка при обновлении проекта: ' . $e->getMessage() . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Редактирование проекта - AI Проекты</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
            <a href="view_project.php?id=<?php echo $project_id; ?>" class="text-decoration-none">
                <i class="bi bi-arrow-left"></i> Назад к просмотру проекта
            </a>
            <h1 class="mt-2">Редактирование проекта</h1>
        </div>
    </div>

    <?php if ($message): ?>
        <?php echo $message; ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Данные проекта</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="project_name" class="form-label">Название проекта</label>
                            <input type="text" id="project_name" name="project_name" class="form-control" 
                                value="<?php echo htmlspecialchars($project['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="question" class="form-label">Запрос к ИИ</label>
                            <textarea id="question" name="question" class="form-control" 
                                rows="5"><?php echo htmlspecialchars($project['question']); ?></textarea>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" name="save_changes" class="btn btn-primary">
                                <i class="bi bi-save"></i> Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Действия</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="view_project.php?id=<?php echo $project_id; ?>" class="btn btn-secondary">
                            <i class="bi bi-eye"></i> Просмотреть проект
                        </a>
                        <button class="btn btn-danger" id="deleteProject">
                            <i class="bi bi-trash"></i> Удалить проект
                        </button>
                        <a href="index.php?duplicate=<?php echo $project_id; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-files"></i> Дублировать проект
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Информация</h5>
                </div>
                <div class="card-body">
                    <p><strong>Создан:</strong> <?php echo date('d.m.Y H:i', strtotime($project['created_at'])); ?></p>
                    <p class="mb-0"><strong>ID проекта:</strong> <?php echo $project_id; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
