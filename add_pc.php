<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавление ПК</title>
</head>
<body>
<?php
// Подключение к базе данных
$conn = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Проверка, были ли переданы данные
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из формы
    $pc_number = isset($_POST['pc_number']) ? trim($_POST['pc_number']) : '';
    $ip_pc = isset($_POST['ip_pc']) ? trim($_POST['ip_pc']) : '';

    // Проверка на пустые значения
    if (empty($pc_number) || empty($ip_pc)) {
        echo "Ошибка: все поля должны быть заполнены.";
        echo '<br><a href="poisk.php">Назад</a>';
        exit;
    }

    // Подготовка и выполнение SQL-запроса
    $stmt = $conn->prepare("INSERT INTO PK (№PC, IPPC) VALUES (?, ?)");
    $stmt->bind_param("ss", $pc_number, $ip_pc);

    if ($stmt->execute()) {
        echo "Новый ПК успешно добавлен.";
    } else {
        echo "Ошибка: " . $stmt->error;
    }

    // Закрытие подготовленного выражения
    $stmt->close();
}

// Закрытие соединения с базой данных
$conn->close();
?>

<a href="poisk.php">Назад</a>
</body>
</html>