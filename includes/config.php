<?php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Замените на вашего пользователя БД
define('DB_PASS', ''); // Замените на ваш пароль БД
define('DB_NAME', 'moyne_sam'); // Замените на имя вашей БД

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Проверка соединения
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Установка кодировки
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    // В реальном проекте лучше логировать ошибку, а не выводить пользователю
    die("Database connection error. Please try again later.");
}
?>