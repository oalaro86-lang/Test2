<?php
$host = "localhost";
$user = "047582029_diplom";
$pass = "Diplom_41";
$db = "j38202257_diplom";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$recipientId = $_GET['user'];

// Запрашиваем сообщения для выбранного получателя
$sql = "SELECT message, timestamp, sender FROM messages WHERE recipient = ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $recipientId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

$stmt->close();
$conn->close();

// Возвращаем сообщения в формате JSON
header('Content-Type: application/json');
echo json_encode($messages);
?>