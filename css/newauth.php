<?php
session_start(); // Начинаем сессию

$login = $_POST['login2'];
$pass = $_POST['pass2'];

$myspl = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

if ($myspl->connect_error) {
    die("Ошибка подключения: " . $myspl->connect_error);
}

$result = $myspl->query("SELECT * FROM `admin` WHERE `login`='$login' AND `pass`='$pass'");
$users = $result->fetch_assoc();

// Проверка на наличие пользователя
if (!$users) {
    echo "Такой пользователь не найден";
    exit();
}

// Устанавливаем сессию для администратора
$_SESSION['admin_logged_in'] = true;

// Закрываем соединение с базой данных
$myspl->close();

// Перенаправляем на poisk.php
header("Location: poisk.php");
exit(); // Завершаем скрипт
?>