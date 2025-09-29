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

// Подключаемся к базе данных
$servername = "localhost";
$username = "047582029_diplom";
$password = "Diplom_41";
$dbname = "j38202257_diplom";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

$categoriy = $_POST['categoriy'] ?? '';
$status = $_POST['status'] ?? '';
$otdel = $_POST['otdel'] ?? '';
$FIO = $_POST['FIO'] ?? '';
$prioritet = $_POST['prioritet'] ?? '';

$sql = "SELECT * FROM `nepoladki` WHERE 1=1";
$params = [];

if (!empty($categoriy)) {
    $sql .= " AND categoriy=?";
    $params[] = $categoriy;
}
if (!empty($status)) {
    $sql .= " AND done=?";
    $params[] = $status;
}
if (!empty($otdel)) {
    $sql .= " AND otdel=?";
    $params[] = $otdel;
}
if (!empty($FIO)) {
    $sql .= " AND FIO=?";
    $params[] = $FIO;
}
if (!empty($prioritet)) {
    $sql .= " AND prioritet=?";
    $params[] = $prioritet;
}

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$output = '';
if ($result->num_rows > 0) {
    $output .= '<table border="1">';
    $output .= '<tr><th>ФИО</th><th>Отдел</th><th>Ошибка</th><th>Категория</th><th>Статус</th><th>Приоритет</th><th>Сетевое имя</th><th>Дата и Время</th><th>Действия</th></tr>';
    while($row = $result->fetch_assoc()) {
        $output .= '<tr>';
        $output .= '<td>' . htmlspecialchars($row["FIO"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["otdel"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["oshibca"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["categoriy"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["done"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["prioritet"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["IPPC"]) . '</td>';
        $output .= '<td>' . htmlspecialchars($row["DateTime"]) . '</td>';
        $output .= '<td>';
        $output .= '<button type="button" onclick="openEditWindow(' . htmlspecialchars($row["id"]) . ')">Выбрать</button>';
        $output .= '</td>';
        $output .= '</tr>';
    }
    $output .= '</table>';
} else {
    $output .= 'Нет заявок для отображения.';
}

echo $output;

$stmt->close();
$conn->close();
?>