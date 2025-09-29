<?php

// Настройки подключения к базе данных
$host = 'localhost';
$db = 'j38202257_diplom';
$user = '047582029_diplom';
$pass = 'Diplom_41';

// Подключение к базе данных
$conn = new mysqli($host, $user, $pass, $db);

// Проверка подключения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Запрос на получение данных
$sql = "SELECT * FROM nepoladki";
$result = $conn->query($sql);

// Проверка наличия данных
if ($result->num_rows > 0) {
    // Создание текстового документа
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="otchet_po_oshibkam.txt"');

    // Вывод заголовков таблицы
    echo "id\     FIO\                            otdel\          categoriy\        oshibca\              done";

    // Вывод данных
    while($row = $result->fetch_assoc()) {
        echo "\n". $row['id'] . "\t" . $row['FIO'] . "\t" . $row['otdel'] . "\t" . $row['categoriy'] . "\t" . "\t" . $row['oshibca'] . "\t" . $row['done']. "\n";
    }
} else {
    echo "No results found.";
}

// Закрытие соединения
$conn->close();



?>