<?php
// Включаем отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Проверяем, авторизован ли пользователь
if (empty($_COOKIE['coock'])) {
    header("Location: index.php"); // Если не авторизован, отправляем на страницу авторизации
    exit();
}

// Получаем имя и отдел пользователя из cookie
$name = htmlspecialchars($_COOKIE['coock']);
$otdel1 = htmlspecialchars($_COOKIE['otdel']);

// Получаем данные из формы
$FIO = $_POST['FIO'] ?? null;
$oshibca = $_POST['oshibca'] ?? null;
$categoriy = $_POST['categoriy'] ?? null;
$prioritet = $_POST['prioritet'] ?? null; // Получаем приоритет из формы
$done = $_POST['done'] ?? null;
$otdel = $_POST['otdel1'] ?? null;

// Проверка на пустое значение
if (empty($FIO)) {
    die("Ошибка: FIO не может быть пустым.");
}

// Подключение к базе данных
$myspl = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

if ($myspl->connect_error) {
    die("Ошибка подключения: " . $myspl->connect_error);
}

// Получаем IPPC для выбранного сотрудника
$ippc_result = $myspl->query("SELECT IPPC FROM users WHERE name='$FIO'");
if (!$ippc_result) {
    die("Ошибка при выполнении запроса: " . $myspl->error);
}

if ($ippc_result->num_rows === 0) {
    die("Ошибка: Сотрудник с таким именем не найден.");
}

$ippc_row = $ippc_result->fetch_assoc();
$ippc = $ippc_row['IPPC'] ?? null;

// Вставляем данные в таблицу nepoladki
$insert_result = $myspl->query("INSERT INTO `nepoladki` (`FIO`, `otdel`, `oshibca`, `categoriy`, `prioritet`, `done`, `IPPC`, `DateTime`) VALUES('$FIO', '$otdel', '$oshibca', '$categoriy', '$prioritet', '$done', '$ippc', NOW())");

if (!$insert_result) {
    die("Ошибка при вставке данных: " . $myspl->error);
}

// Закрываем соединение с базой данных
$myspl->close();

// Перенаправляем пользователя в зависимости от отдела
if ($otdel1 === 'Техническая поддержка') {
    header("Location: poisk.php");
} else {
    header("Location: menu.php");
}
exit();
?>