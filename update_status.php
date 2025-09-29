<?php
$conn = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Установка кодировки соединения с БД на UTF-8
$conn->set_charset("utf8");

// Получаем данные из формы
$id = $_POST['id'] ?? null;
$done = $_POST['done'] ?? null;
$prioritet = $_POST['prioritet'] ?? null;

if ($id && $done && $prioritet) {
    // Обновляем запись в базе данных
    $stmt = $conn->prepare("UPDATE `nepoladki` SET done = ?, prioritet = ? WHERE id = ?");
    $stmt->bind_param("ssi", $done, $prioritet, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Заявка успешно обновлена.";
    } else {
        echo "Ошибка при обновлении заявки.";
    }

    $stmt->close();
} else {
    echo "Не все данные были переданы.";
}

$conn->close();
?>