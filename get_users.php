<?php
// Проверяем, был ли передан параметр otdel
if (isset($_POST['otdel'])) {
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

    $otdel = htmlspecialchars($_POST['otdel']);
    // Запрос на получение пользователей из выбранного отдела
    $sql = "SELECT name FROM users WHERE otdel = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $otdel);
    $stmt->execute();
    $result = $stmt->get_result();

    // Формируем HTML для списка пользователей
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
        }
    } else {
        echo "<option value=''>Нет доступных пользователей</option>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<option value=''>Ошибка: отдел не выбран</option>";
}
?>