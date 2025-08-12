<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Получение информации о пользователе
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Проекты - Генератор</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">ИИ Ассистент</h4>
                    <button id="saveProject" class="btn btn-light btn-sm">Сохранить проект</button>
                </div>
                <div class="card-body">
                    <div id="answer" class="border rounded p-3 mb-4 bg-white">
                        <div class="text-center text-muted">
                            <div class="spinner-border text-primary me-2" role="status"></div>
                            <span>Запрашиваем название проекта...</span>
                        </div>
                    </div>
                    
                    <form id="aiForm">
                        <div class="mb-3">
                            <textarea name="question" class="form-control" 
                                      placeholder="Введите ваш вопрос..." rows="5"></textarea>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Отправить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<script>
// Функция для запроса к API
async function sendQuestion(question) {
    const answerDiv = document.getElementById('answer');
    answerDiv.innerHTML = `<div class="d-flex align-items-center">
                            <div class="spinner-border text-primary me-2" role="status"></div>
                            <span>Обработка запроса...</span>
                           </div>`;

    try {
        const response = await fetch("https://itsa777.app.n8n.cloud/webhook/5e1d9bad-433a-43b7-a406-1295aff6c7f0", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ question: question })
        });

        if (!response.ok) {
            answerDiv.innerHTML = `<div class="alert alert-danger">Ошибка запроса: ${response.status}</div>`;
            return;
        }

        const result = await response.json();
        console.log("Полученные данные от API:", result);
        
        if (result.output) {
            // Проверяем тип данных result.output
            if (typeof result.output === 'string') {
                // Если строка - обрабатываем как раньше
                // Проверяем, что result.output является строкой
                if (typeof result.output === 'string') {
                    // Если это markdown-блок с json, парсим его
                    const match = result.output.match(/```json\s*([\s\S]*?)```/);
                    if (match) {
                        answerDiv.innerHTML = `<pre class="bg-light p-3 rounded">${match[1]}</pre>`;

                        try {
                            const responseApi1 = await fetch("https://itsa777.app.n8n.cloud/webhook/654ca023-d8a1-47c7-ba21-c7d6d746ea51", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ response: match[1] })
                        });

                        if (!responseApi1.ok) {
                            answerDiv.innerHTML = `<div class="alert alert-danger">Ошибка запроса responseApi1: ${responseApi1.status}</div>`;
                            return;
                        }

                        const resultApi1 = await responseApi1.json();
                        
                        console.log(resultApi1);

                        // Store the current HTML content
                        const currentContent = answerDiv.innerHTML;

                        console.log("Полученные данные:", resultApi1.data);
                        
                        // Проверяем и исправляем формат данных изображения
                        if (resultApi1.data) {
                            let imageData = resultApi1.data;
                            
                            // Удаляем символ "=" в начале строки, если он есть
                            if (imageData.startsWith('=')) {
                                imageData = imageData.substring(1);
                                console.log("Удален префикс =, новые данные:", imageData);
                            }
                            
                            // Проверяем, является ли это data URI изображения
                            if (typeof imageData === 'string' && imageData.startsWith('data:image')) {
                                // Добавляем изображение к существующему содержимому
                                answerDiv.innerHTML = currentContent + `<div class="mt-3 text-center">
                                    <img src="${imageData}" alt="AI image" class="img-fluid rounded shadow-sm" style="max-width:100%;">
                                </div>`;
                                console.log("Изображение добавлено в DOM, длина данных: " + imageData.length);
                            } else {
                                console.error("Данные изображения не в ожидаемом формате:", imageData);
                                answerDiv.innerHTML += `<div class="alert alert-warning mt-3">Ошибка формата данных изображения</div>`;
                            }
                        } else {
                            console.error("Данные изображения отсутствуют в ответе:", resultApi1);
                        }

                    } catch (err) {
                        console.error("Error processing image:", err);
                        answerDiv.innerHTML += `<div class="alert alert-danger mt-3">Ошибка при обработке изображения: ${err.message}</div>`;
                    }
                } else {
                    answerDiv.innerHTML = `<div class="p-3 bg-white rounded">${result.output}</div>`;
                }
            } else {
                // Если result.output не строка, выводим его как JSON
                console.warn("result.output не является строкой:", typeof result.output);
                answerDiv.innerHTML = `<div class="alert alert-warning">Получен ответ в неожиданном формате</div>
                                     <pre class="bg-light p-3 rounded">${JSON.stringify(result, null, 2)}</pre>`;
            }
        } else if (typeof result.output === 'object') {
                // Если это объект - работаем с ним напрямую
                console.log("result.output является объектом:", result.output);
                
                // Преобразуем объект в JSON строку для отображения
                const jsonString = JSON.stringify(result.output, null, 2);
                answerDiv.innerHTML = `<pre class="bg-light p-3 rounded">${jsonString}</pre>`;
                
                try {
                    // Отправляем данные во второй API
                    const responseApi1 = await fetch("https://itsa777.app.n8n.cloud/webhook/654ca023-d8a1-47c7-ba21-c7d6d746ea51", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        // Отправляем строковое представление объекта
                        body: JSON.stringify({ response: jsonString })
                    });

                    if (!responseApi1.ok) {
                        answerDiv.innerHTML += `<div class="alert alert-danger mt-3">Ошибка запроса к API изображений: ${responseApi1.status}</div>`;
                        return;
                    }

                    const resultApi1 = await responseApi1.json();
                    console.log("Ответ от API изображений:", resultApi1);

                    // Сохраняем текущее содержимое
                    const currentContent = answerDiv.innerHTML;
                    
                    // Обработка полученного изображения - такая же как раньше
                    if (resultApi1.data) {
                        let imageData = resultApi1.data;
                        
                        if (imageData.startsWith('=')) {
                            imageData = imageData.substring(1);
                        }
                        
                        if (typeof imageData === 'string' && imageData.startsWith('data:image')) {
                            answerDiv.innerHTML = currentContent + `<div class="mt-3 text-center">
                                <img src="${imageData}" alt="AI image" class="img-fluid rounded shadow-sm" style="max-width:100%;">
                            </div>`;
                        } else {
                            console.error("Данные изображения не в ожидаемом формате:", imageData);
                            answerDiv.innerHTML += `<div class="alert alert-warning mt-3">Ошибка формата данных изображения</div>`;
                        }
                    } else {
                        console.error("Данные изображения отсутствуют в ответе:", resultApi1);
                    }
                } catch (err) {
                    console.error("Ошибка при обработке изображения:", err);
                    answerDiv.innerHTML += `<div class="alert alert-danger mt-3">Ошибка при обработке изображения: ${err.message}</div>`;
                }
            } else {
                // Если другой тип данных
                console.warn("result.output имеет неожиданный тип:", typeof result.output);
                answerDiv.innerHTML = `<div class="alert alert-warning">Получен ответ в неожиданном формате (тип: ${typeof result.output})</div>
                                      <pre class="bg-light p-3 rounded">${JSON.stringify(result, null, 2)}</pre>`;
            }
        } else {
            answerDiv.innerHTML = `<pre class="bg-light p-3 rounded">${JSON.stringify(result, null, 2)}</pre>`;
        }

    } catch (err) {
        answerDiv.innerHTML = `<div class="alert alert-danger">Ошибка: ${err.message}</div>`;
    }
}

// Обработчик отправки формы пользователем
document.getElementById('aiForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const question = e.target.question.value;
    await sendQuestion(question);
});

// Автоматически запрашиваем название проекта при загрузке страницы
document.addEventListener('DOMContentLoaded', async function() {
    // Небольшая задержка для лучшего UX
    setTimeout(async () => {
        // Отправляем запрос для получения названия проекта
        await sendQuestion('Введите название проекта');
    }, 500);
});

// Добавляем функционал сохранения проекта
document.getElementById('saveProject').addEventListener('click', async function() {
    // Запрашиваем название проекта у пользователя
    let projectName = prompt('Введите название проекта:');
    
    // Проверяем, был ли отменен ввод или введена пустая строка
    if (projectName === null) {
        // Пользователь отменил ввод
        return;
    }
    
    // Удаляем лишние пробелы и проверяем, не пустое ли название
    projectName = projectName.trim();
    
    if (projectName === '') {
        // Если название пустое, используем текущую дату и время
        const now = new Date();
        projectName = 'Проект от ' + now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
    }
    
    // Если название слишком длинное, обрезаем его
    if (projectName.length > 100) {
        projectName = projectName.substring(0, 97) + '...';
    }
    
    const content = document.getElementById('answer').innerHTML;
    const question = document.querySelector('textarea[name="question"]').value;
    
    try {
        const response = await fetch('save_project.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                name: projectName,
                content: content,
                question: question
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Проект успешно сохранен!');
        } else {
            alert('Ошибка при сохранении проекта: ' + result.error);
        }
    } catch (error) {
        alert('Произошла ошибка: ' + error.message);
    }
});
</script>

</body>
</html>