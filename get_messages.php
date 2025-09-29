
<?php
// Проверяем, авторизован ли пользователь
if (empty($_COOKIE['coock'])) {
    http_response_code(401);
    exit();
}

$name = htmlspecialchars($_COOKIE['coock']);
$recipient = isset($_GET['recipient']) ? htmlspecialchars($_GET['recipient']) : '';

$servername = "localhost";
$username = "047582029_diplom";
$password = "Diplom_41";
$dbname = "j38202257_diplom";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$sql = "SELECT sender, message, timestamp FROM messages WHERE (sender = ? AND recipient = ?) OR (sender = ? AND recipient = ?) ORDER BY timestamp";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $name, $recipient, $recipient, $name);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

$stmt->close();
$conn->close();

// Возвращаем сообщения в JSON-формате
header('Content-Type: application/json');
echo json_encode($messages);
?>