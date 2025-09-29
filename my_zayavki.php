<?php
// Старт сессии
session_start();

// Проверка, авторизован ли пользователь через cookies
if (!isset($_COOKIE['coock'])) {
    header("Location: index.php"); // Перенаправление на страницу авторизации, если пользователь не авторизован
    exit();
}

// Подключение к базе данных
$servername = "localhost";
$username = "047582029_diplom";
$password = "Diplom_41";
$dbname = "j38202257_diplom";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение Ф.И.О. из cookie
$user_fio = $_COOKIE['coock'];

// Запрос на получение заявок, принадлежащих пользователю
$sql = "SELECT * FROM nepoladki WHERE FIO = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_fio);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявки</title>
    <link rel="stylesheet" href="css/help.css">
    <style>
        body {
            background-color: #f8f9fa; /* Фоновый цвет для страницы */
            font-family: Arial, sans-serif; /* Шрифт страницы */
        }

        .container {
            max-width: 800px; /* Максимальная ширина бокса */
            margin: 0 auto; /* Центрирование бокса */
            padding: 20px; /* Отступы внутри бокса */
            background-color: #000; /* Черный фон */
            color: white; /* Белый текст */
            border-radius: 10px; /* Закругленные углы */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5); /* Тень для бокса */
        }

        table {
            width: 100%; /* Занимаем всю ширину контейнера */
            border-collapse: collapse; /* Убираем двойные границы */
        }

        th, td {
            padding: 10px; /* Отступы внутри ячеек */
            text-align: left; /* Выравнивание текста в ячейках */
        }

        th {
            background-color: #444; /* Цвет фона заголовков */
        }

        tr:nth-child(even) {
            background-color: #222; /* Цвет для четных строк таблицы */
        }

        a {
            color: white; /* Белый цвет текста для ссылок */
            text-decoration: none; /* Убираем подчеркивание */
        }

        a:hover {
            text-decoration: underline; /* Подчеркивание при наведении */
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1>Мои заявки</h1>
    <table border="1">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ф.И.О.</th>
                <th>Отдел</th>
                <th>Категория</th>
                <th>Ошибка</th>
                <th>Статус</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Проверяем, есть ли заявки
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['FIO']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['otdel']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['categoriy']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['oshibca']) . "</td>";
                    echo "<td>" . ($row['done']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Нет доступных заявок</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <br>
    <a href="menu.php">Назад</a>
</div>
</body>
</html>

<?php
// Закрытие соединения с базой данных
$conn->close();
?>