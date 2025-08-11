<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Получаем все проекты пользователя
$stmt = $pdo->prepare("SELECT id, name, created_at FROM projects WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Мои проекты - AI Проекты</title>
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
                    <a class="nav-link active" href="projects.php">Мои проекты</a>
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
            <h1>Мои проекты</h1>
        </div>
        <div class="col-auto">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Новый проект
            </a>
        </div>
    </div>
    
    <?php if (empty($projects)): ?>
        <div class="alert alert-info">
            У вас пока нет проектов. <a href="index.php">Создайте первый проект</a>!
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($projects as $project): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($project['name']); ?></h5>
                            <p class="card-text text-muted small">
                                Создан: <?php echo date('d.m.Y H:i', strtotime($project['created_at'])); ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Просмотр
                            </a>
                            <button class="btn btn-sm btn-danger delete-project" data-id="<?php echo $project['id']; ?>">
                                <i class="bi bi-trash"></i> Удалить
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Обработка удаления проекта
document.querySelectorAll('.delete-project').forEach(button => {
    button.addEventListener('click', async function() {
        if (confirm('Вы уверены, что хотите удалить этот проект?')) {
            const projectId = this.dataset.id;
            
            try {
                const response = await fetch('delete_project.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: projectId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Удаляем карточку проекта из DOM
                    this.closest('.col').remove();
                    
                    // Если проектов больше нет, показываем сообщение
                    if (document.querySelectorAll('.col').length === 0) {
                        const container = document.querySelector('.container');
                        container.innerHTML += `
                            <div class="alert alert-info">
                                У вас пока нет проектов. <a href="index.php">Создайте первый проект</a>!
                            </div>
                        `;
                    }
                } else {
                    alert('Ошибка при удалении проекта: ' + result.error);
                }
            } catch (error) {
                alert('Произошла ошибка: ' + error.message);
            }
        }
    });
});
</script>
</body>
</html>
