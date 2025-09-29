<?php
    // Подключение к базе данных
    $servername = "localhost";
    $username = "047582029_diplom";
    $password = "Diplom_41";
    $dbname = "j38202257_diplom";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Получение данных из формы
    $fio = $_POST['fio'];
    $otdel = $_POST['otdel'];
    $oshibca = $_POST['oshibca'];
    $categoriy = $_POST['categoriy'];
    $done = 0; // Изначально задача не выполнена

    // Подготовка и выполнение запроса на вставку данных в таблицу
    $sql = "INSERT INTO nepoladki (FIO, otdel, oshibca, categoriy, done)
            VALUES ('$fio', '$otdel', '$oshibca', '$categoriy', $done)";

    if ($conn->query($sql) === TRUE) {
        echo "Вы успешно отправили заявку! Теперь Вы можете вернуться в меню. <a href='/modKR/menu.php'>Главная страница</a>";
    } else {
        echo "Ошибка: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
?>