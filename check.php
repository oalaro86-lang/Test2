<?php
$login = $_POST['login'];
$name = $_POST['name1'];
$otdel = $_POST['otdel'];
$pass = $_POST['pass'];
$pc_number = $_POST['pc_number'];

// Подключение к базе данных
$myspl = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

// Проверка на наличие отдела
$result = $myspl->query("SELECT * FROM `otdel` WHERE `name`='$otdel'");
$users = $result->fetch_assoc();
if (count($users) == 0) {
    echo "Такой отдел не найден $otdel";
    exit();
}

// Проверка длины данных
if (mb_strlen($login) < 5 || mb_strlen($login) > 90) {
    echo "Недопустимая длина логина";
    exit();
} else if (mb_strlen($name) < 3 || mb_strlen($name) > 500) {
    echo "Недопустимая длина имени<br> $name";
    exit();
} else if (mb_strlen($pass) < 2 || mb_strlen($pass) > 6) {
    echo "Недопустимая длина пароля (от 2 до 6 символов)";
    exit();
}

// Хеширование пароля
$pass = md5($pass . "ghjsfkld2345");

// Получение IPPC по №PC
$result_pc = $myspl->query("SELECT `IPPC` FROM `PK` WHERE `№PC`='$pc_number'");
$ip_result = $result_pc->fetch_assoc();
if (!$ip_result) {
    echo "Номер ПК не найден.";
    exit();
}
$ip_address = $ip_result['IPPC'];

// Вставка данных в таблицу users
$myspl->query("INSERT INTO `users` (`login`, `otdel`, `pass`, `name`, `IPPC`) VALUES('$login', '$otdel', '$pass', '$name', '$ip_address')");
echo $myspl->error;

$myspl->close();

echo "Вы успешно зарегистрированы! Теперь Вы можете зайти на сайт. <a href='index.php'>Главная страница</a>";
?>
