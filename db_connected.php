<?php
// Подключение к файлу с параметрами базы данных
require_once 'db.php';

// Создание подключения к базе данных
$conn = new mysqli($myserver, $mylogin, $mypassword, "girlfeeria");

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Добавление поля color в таблицы товаров, если его нет
$tables = ['futbolki', 'kofty', 'bryuki'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW COLUMNS FROM $table LIKE 'color'");
    if ($result->num_rows === 0) {
        $conn->query("ALTER TABLE $table ADD COLUMN color VARCHAR(50) DEFAULT 'не указан'");
    }
}
?>
