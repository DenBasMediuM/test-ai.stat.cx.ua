<?php
// Отображаем все ошибки для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем файл конфигурации базы данных
require_once 'db.php';

echo "<h1>Установка базы данных</h1>";

// Проверяем существование таблиц
try {
    // Проверяем таблицу users
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    $usersExists = $stmt->rowCount() > 0;
    
    // Проверяем таблицу projects
    $stmt = $pdo->query("SHOW TABLES LIKE 'projects'");
    $projectsExists = $stmt->rowCount() > 0;
    
    echo "<h2>Статус таблиц:</h2>";
    echo "<p>Таблица 'users': " . ($usersExists ? "<span style='color:green'>Существует</span>" : "<span style='color:red'>Отсутствует</span>") . "</p>";
    echo "<p>Таблица 'projects': " . ($projectsExists ? "<span style='color:green'>Существует</span>" : "<span style='color:red'>Отсутствует</span>") . "</p>";
    
    // Если таблиц нет, предлагаем их создать
    if (!$usersExists || !$projectsExists) {
        echo "<form method='post'>";
        echo "<input type='hidden' name='create_tables' value='1'>";
        echo "<button type='submit' style='padding:10px;font-size:16px;background-color:#4CAF50;color:white;border:none;cursor:pointer;'>Создать отсутствующие таблицы</button>";
        echo "</form>";
    } else {
        echo "<p style='color:green'>Все необходимые таблицы существуют!</p>";
    }
    
    // Если была отправлена форма для создания таблиц
    if (isset($_POST['create_tables'])) {
        // Создаем таблицу users, если она отсутствует
        if (!$usersExists) {
            $pdo->exec("CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            echo "<p style='color:green'>Таблица 'users' успешно создана!</p>";
        }
        
        // Создаем таблицу projects, если она отсутствует
        if (!$projectsExists) {
            $pdo->exec("CREATE TABLE projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                content TEXT NOT NULL,
                question TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )");
            echo "<p style='color:green'>Таблица 'projects' успешно создана!</p>";
        }
        
        echo "<p><a href='install.php'>Обновить страницу</a> для проверки статуса.</p>";
    }
    
    // Добавим ссылку на главную
    echo "<p><a href='index.php'>Перейти на главную страницу</a></p>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red'>Ошибка:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
