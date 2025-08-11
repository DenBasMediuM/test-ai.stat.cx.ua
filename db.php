<?php
// Настройки подключения к базе данных
$host = 'avalon.cityhost.com.ua';
$dbname = 'ch29f38bbe_test-ai';
$username = 'ch29f38bbe_test-ai';
$password = '8q1ruYBR2N';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Проверяем существование необходимых таблиц
    createTablesIfNotExist($pdo);
    
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Функция для проверки и создания таблиц
function createTablesIfNotExist($pdo) {
    try {
        // Проверяем существование таблицы users
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() == 0) {
            // Создаем таблицу пользователей
            $pdo->exec("CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
        }
        
        // Проверяем существование таблицы projects
        $stmt = $pdo->query("SHOW TABLES LIKE 'projects'");
        if ($stmt->rowCount() == 0) {
            // Создаем таблицу проектов с LONGTEXT для контента
            $pdo->exec("CREATE TABLE projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                content LONGTEXT NOT NULL,
                question TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )");
        } else {
            // Проверяем тип поля content и изменяем на LONGTEXT если нужно
            try {
                $pdo->exec("ALTER TABLE projects MODIFY content LONGTEXT NOT NULL");
                error_log("Поле content изменено на LONGTEXT");
            } catch (PDOException $e) {
                error_log("Ошибка при изменении поля content: " . $e->getMessage());
            }
        }
    } catch (PDOException $e) {
        // Логируем ошибку, но не останавливаем выполнение скрипта
        error_log("Ошибка при создании таблиц: " . $e->getMessage());
    }
}

// Оставляем SQL для справки
/*
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    question TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
*/
?>
