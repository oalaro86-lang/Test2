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
$otdel = htmlspecialchars($_COOKIE['otdel']);

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

// Получаем IP-адрес
$sql = "SELECT IPPC FROM users WHERE name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();
$stmt->bind_result($ip);
$stmt->fetch();
$stmt->close();

// Получаем список пользователей из отдела Техническая поддержка
$sql = "SELECT name FROM users WHERE otdel = 'Техническая поддержка' AND name != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Инициализируем переменные для сообщений
$messages = [];

// Проверяем, была ли отправлена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient']) && isset($_POST['message'])) {
    $recipient = htmlspecialchars($_POST['recipient']);
    $message = htmlspecialchars($_POST['message']);

    // Вставляем сообщение в базу данных
    $sql = "INSERT INTO messages (sender, recipient, message, timestamp) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $recipient, $message);
    $stmt->execute();
    $stmt->close();
}

// Получаем сообщения для выбранного собеседника, если он установлен
if (isset($_GET['recipient'])) {
    $recipient = htmlspecialchars($_GET['recipient']);
    $sql = "SELECT sender, message, timestamp FROM messages WHERE (sender = ? AND recipient = ?) OR (sender = ? AND recipient = ?) ORDER BY timestamp";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $recipient, $recipient, $name);
    $stmt->execute();
    $result = $stmt->get_result();
    $messages = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Получаем заявки пользователя
$sql = "SELECT * FROM nepoladki WHERE FIO = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Форма управления</title>
    <style>
        .container { width: 1000px; margin: 0 auto; }
        .container.mt-4 { margin: 20px auto; }
        .tab { display: none; }
        .tab.active { display: block; }
        .tab-button { cursor: pointer; margin-right: 5px; }
    .container select {
    width: 300px; /* Устанавливаем ширину 300 пикселей */
    padding: 5px; /* Добавляем отступы для улучшения внешнего вида */
    border: 1px solid #ccc; /* Добавляем рамку */
    border-radius: 4px; /* Скругляем углы рамки */
    font-size: 16px; /* Устанавливаем размер шрифта */
    background-color: #fff; /* Устанавливаем цвет фона */
    appearance: none; /* Убираем стандартный стиль браузера */
    background-image: url('path/to/arrow-icon.png'); /* Добавляем иконку стрелки (если нужно) */
    background-repeat: no-repeat; /* Не повторяем изображение */
    background-position: right 10px center; /* Устанавливаем позицию иконки */
}
    </style>
    <style>
        .tab {
            display: none; /* Скрываем все вкладки по умолчанию */
        }
        .tab.active {
            display: block; /* Показываем только активную вкладку */
        }
        .tab-button {
            cursor: pointer;
            margin-right: 10px;
        
            
            
        }
    </style>
    <script>
        function showTab(tabId) {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
        }
        function openEditWindow(id) {
            window.open('edit_zayavka.php?id=' + id, 'Редактировать заявку', 'width=600,height=400');
        }
    </script>
</head>
<body>
<div class="container">
    <div class="name-field">
        <label for="name">ФИО:</label>
        <input type="text" id="name" value="<?php echo $name; ?>" readonly><br>
        <label for="otdel">Отдел:</label>
        <input type="text" id="otdel" value="<?php echo $otdel; ?>" readonly><br>
        <label for="ip">Сетевое имя:</label>
        <input type="text" id="ip" value="<?php echo isset($ip) ? htmlspecialchars($ip) : ''; ?>" readonly><br><br>
    </div>
    
    <div class="box-field">
        <button class="tab-button" onclick="showTab('my_requests')">Мои заявки</button>
        <button class="tab-button" onclick="showTab('add_request')">Добавить новую заявку</button>
        <button class="tab-button" onclick="showTab('filtr')">Фильтр заявок</button><br><br>
        <button class="tab-button" onclick="showTab('export_data')">Экспорт данных</button>
        <button class="tab-button" onclick="showTab('add_pc')">Добавить новый ПК</button>
        
        <button class="tab-button" onclick="showTab('exit')">Выход</button><br><br>
        <p><a href="admin_chat.php">Чат</a></p>

</div>

        <div id="my_requests" class="tab">
            <link rel="stylesheet" href="styles.css">
            <h2>Мои заявки</h2>
            <table border="1" style="width:70%; border-collapse:collapse;">
                <div class="container_mt-4">
                    <table border="1" style="width:70%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ф.И.О.</th>
                                <th>Отдел</th>
                                <th>Категория</th>
                                <th>Ошибка</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['FIO']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['otdel']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['categoriy']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['oshibca']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['done']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6'>Нет доступных заявок</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <br>
                </div>
            </div>
            </table>
        </div>






        <div id="add_request" class="tab">
            
            
            
       
            
            
            
            
            
            
            
            
            
            
            
            <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>HelpDesk</title>

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
               
            </form>
        </div>

        <div id="export_data" class="tab">
            <link rel="stylesheet" href="styles.css">
            <h2>Экспорт данных о заявках</h2>
            <a href="export.php"><button>Скачать данные</button></a>
        </div>

        <div id="add_pc" class="tab">
            <link rel="stylesheet" href="styles.css">
            <h2>Добавить новый ПК</h2>
            <form action="add_pc.php" method="post">
                <label for="pc_number">Номер компьютера:</label>
                <input type="text" id="pc_number" name="pc_number" required>
                
                <label for="ip_pc">Сетевое имя компьютера:</label>
                <input type="text" id="ip_pc" name="ip_pc" required>
                
                <button type="submit">Добавить</button>
            </form>
        </div>


<div id="filtr" class="tab active">
    <link rel="stylesheet" href="styles.css">
    <h1>Фильтр заявок</h1>
    <form action="poisk.php" method="post"> <!-- Измените на poisk.php -->
        <label for="categoriy">Категория:</label>
        <select id="categoriy" name="categoriy" required>
            <option value="">Выберите категорию</option>
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

            // Получение категорий из таблицы problems
            $sql = "SELECT DISTINCT name FROM problems";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row["name"]) . "'>" . htmlspecialchars($row["name"]) . "</option>";
                }
            } else {
                echo "<option value=''>Нет доступных категорий</option>";
            }
            ?>
        </select><br><br>

        <label for="status">Статус:</label>
        <select id="status" name="status">
            <option value="">Выберите статус</option>
            <?php
            // Получение статусов из таблицы status
            $sqlStatus = "SELECT DISTINCT name FROM status";
            $statusResult = $conn->query($sqlStatus);
            if ($statusResult->num_rows > 0) {
                while ($statusRow = $statusResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($statusRow["name"]) . "'>" . htmlspecialchars($statusRow["name"]) . "</option>";
                }
            }
            ?>
        </select><br><br>

        <label for="otdel">Отдел:</label>
        <select id="otdel" name="otdel">
            <option value="">Выберите отдел</option>
            <?php
            // Получение названий отделов из таблицы otdel
            $sqlOtdel = "SELECT DISTINCT name FROM otdel";
            $otdelResult = $conn->query($sqlOtdel);

            if ($otdelResult->num_rows > 0) {
                while ($otdelRow = $otdelResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($otdelRow["name"]) . "'>" . htmlspecialchars($otdelRow["name"]) . "</option>";
                }
            } else {
                echo "<option value=''>Нет доступных отделов</option>";
            }
            ?>
        </select><br><br>

        <label for="prioritet">Приоритет:</label>
        <select id="prioritet" name="prioritet">
            <option value="">Выберите приоритет</option>
            <?php
            // Получение приоритетов из таблицы prioritet
            $sqlPrioritet = "SELECT DISTINCT name FROM prioritet";
            $prioritetResult = $conn->query($sqlPrioritet);

            if ($prioritetResult->num_rows > 0) {
                while ($prioritetRow = $prioritetResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($prioritetRow["name"]) . "'>" . htmlspecialchars($prioritetRow["name"]) . "</option>";
                }
            } else {
                echo "<option value=''>Нет доступных приоритетов</option>";
            }
            ?>
        </select><br><br>

        <label for="FIO">Имя сотрудника:</label>
        <input type="text" id="FIO" name="FIO"><br><br>

        <button class="btn btn-success" type="submit">Поиск</button><br><br>
    </form>

    <form method="POST" action="update_status.php"> <!-- Форма для отправки данных -->
        <?php
        $categoriy = $_POST['categoriy'] ?? '';
        $status = $_POST['status'] ?? '';
        $otdel = $_POST['otdel'] ?? '';
        $FIO = $_POST['FIO'] ?? '';
        $prioritet = $_POST['prioritet'] ?? '';

        $conn = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $conn->set_charset("utf8");

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

        if ($result->num_rows > 0) {
            echo '<table border="1">';
            echo '<tr><th>ФИО</th><th>Отдел</th><th>Ошибка</th><th>Категория</th><th>Статус</th><th>Приоритет</th><th>Сетевое имя</th><th>Дата и Время</th><th>Действия</th></tr>';
            while($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row["FIO"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["otdel"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["oshibca"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["categoriy"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["done"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["prioritet"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["IPPC"]) . '</td>';
                echo '<td>' . htmlspecialchars($row["DateTime"]) . '</td>';
                echo '<td>';
                echo '<button type="button" onclick="openEditWindow(' . htmlspecialchars($row["id"]) . ')">Выбрать</button>';
                
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo 'Нет заявок для отображения.';
        }

        $stmt->close();
        $conn->close();
        ?>
    </form>
</div>



        <div id="exit" class="tab">
            <h2>Выход</h2>
            <p>Вы действительно хотите выйти? <a href="exit.php">Да</a></p>
        </div>
    </div>
</div>

</body>
</html>
