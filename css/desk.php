<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HelpDesk</title>
    <link rel="stylesheet" href="css/help.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#otdel1').change(function() {
                var selectedOtdel = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: 'get_users.php',
                    data: { otdel: selectedOtdel },
                    success: function(response) {
                        $('#FIO').html(response);
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <div class="login-box">
                    <h1>HelpDesk</h1>
                    <form action="save.php" method="post">
                        <label for="otdel"></label>
                        <select id="otdel1" name="otdel1" required>
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

                                // Получение данных отделов
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

                        <label for="users"></label>
                        <select id="FIO" name="FIO" required>
                            <option value="">Выберите ФИО</option>
                        </select><br><br>

                        <input type="text" class="form-control" name="oshibca" id="oshibca" placeholder="Опишите проблему"><br><br>

                        <label for="categoriy"></label>
                        <select id="categoriy" name="categoriy" required>
                            <option value="">Выберите категорию</option>
                            <?php
                                // Подключение к базе данных
                                $conn = new mysqli($servername, $username, $password, $dbname);
                                if ($conn->connect_error) {
                                    die("Ошибка подключения: " . $conn->connect_error);
                                }

                                // Получение категорий из таблицы problems
                                $sql = "SELECT DISTINCT name FROM problems";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>Нет доступных категорий</option>";
                                }

                                $conn->close();
                            ?>
                        </select><br><br>





                        <label for="prioritet"></label>
<select id="prioritet" name="prioritet" required>
    <option value="">Выберите приоритет</option>
    <?php
        // Подключение к базе данных
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            die("Ошибка подключения: " . $conn->connect_error);
        }

        // Получение приоритетов из таблицы prioritet
        $sql = "SELECT DISTINCT name FROM prioritet";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";
            }
        } else {
            echo "<option value=''>Нет доступных значений</option>";
        }

        $conn->close();
    ?>
</select><br><br>















                        <label for="done"></label>
                        <select id="done" name="done" required disabled>
                            <option value="отправлено" selected>отправлено</option>
                        </select><br><br>

                        <input type="hidden" name="done" value="отправлено"><!-- Скрытое поле для статуса -->

                        <button class="btn btn-success" type="submit">Сохранить</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>