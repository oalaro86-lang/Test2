<?php
session_start();

$myspl = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

if ($myspl->connect_error) {
    die("Ошибка подключения: " . $myspl->connect_error);
}

// Получаем текущее время
$currentTime = date('Y-m-d H:i:s');

// Вычисляем время 30 секунд назад
$timeLimit = date('Y-m-d H:i:s', strtotime($currentTime) - 30);

// Получаем новые записи за последние 30 секунд
$query = "SELECT * FROM `nepoladki` WHERE `DateTime` > '$timeLimit'";
$result = $myspl->query($query);

$newRecords = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $newRecords[] = $row;
    }
}

// Возвращаем новые записи в формате JSON
echo json_encode($newRecords);

$myspl->close();
?>