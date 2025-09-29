<?php
$login = $_POST['login'];
$pass = $_POST['pass'];

// Хешируем пароль
$pass = md5($pass . "ghjsfkld2345");

// Подключаемся к базе данных
$myspl = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

// Проверяем соединение
if ($myspl->connect_error) {
    die("Ошибка подключения: " . $myspl->connect_error);
}

// Выполняем запрос к базе данных
$result = $myspl->query("SELECT * FROM users WHERE login='$login' AND pass='$pass'");

// Получаем данные пользователя
$users = $result->fetch_assoc();

// Проверяем, найден ли пользователь
if (!$users) {
    echo "Такой пользователь не найден";
    exit();
}

// Получаем имя пользователя и отдел
$name = $users['name'];
$otdel = $users['otdel'];

// Устанавливаем cookie для идентификации пользователя
setcookie('coock', $name, time() + 3600, "/"); // Cookie будет действовать 1 час
setcookie('otdel', $otdel, time() + 3600, "/"); // Cookie для отдела

// Закрываем соединение с базой данных
$myspl->close();

// Перенаправляем на нужную страницу в зависимости от отдела
if ($otdel === 'Техническая поддержка') {
    $_SESSION['admin_logged_in'] = true;
    header("Location: poisk.php");
} else {
    header("Location: menu.php");
}
exit();
?>