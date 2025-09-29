<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">




    <title>Форма регистрации</title>
    
    <link rel="stylesheet" href="css/help.css"
    </head>
    <body>
    <div class="container mt-4">
    
    <div class="row">
    <div class="col">
<div class="login-box">

    

        <h1>Форма регистрации</h1>
            <form action="check.php" method="post">
    <div class="user-box">
        <input type="text" class="form-control" name="login" id="login" placeholder="Введите логин"><br>
    </div>
    <div class="user-box">
        <input type="text" class="form-control" name="name1" id="name" placeholder="Введите полное имя"><br>
    </div>
    <label for="otdel"></label>
    <select id="otdel" name="otdel" required>
        <option value="">Выберите отдел</option>
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



                // Получение данных

                $sql = "SELECT DISTINCT name FROM otdel";

                $result = $conn->query($sql);



                if ($result->num_rows > 0) {

                    while($row = $result->fetch_assoc()) {

                        echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";

                    }

                } else {

                    echo "<option value=''>Нет доступных отделов</option>";

                }



                $conn->close();

            ?>
    </select><br><br>
    <div class="user-box">
        <input type="password" class="form-control" name="pass" id="pass" placeholder="Введите пароль"><br>
    </div>
    <div class="user-box">
        <input type="text" class="form-control" name="pc_number" id="pc_number" placeholder="Введите номер компьютера"><br>
    </div>
    <button class="btn btn-success" type="submit">Авторизоваться</button><br>
</form>


</div>



</div>



</html>

