<?php
$host = "localhost";
$user = "047582029_diplom";
$pass = "Diplom_41";
$db = "j38202257_diplom";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$name = isset($_COOKIE['coock']) ? htmlspecialchars($_COOKIE['coock']) : null;

if (!$name) {
    die(json_encode([])); // Если пользователь не авторизован, возвращаем пустой массив
}


$lastMessageId = isset($_GET['last_message_id']) ? intval($_GET['last_message_id']) : 0;

$sql = "SELECT * FROM messages WHERE (recipient = ? OR sender = ?) AND is_read = 0 AND id > ? ORDER BY timestamp DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $name, $name, $lastMessageId);

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($messages);
?>