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
} else {
    // Если нет выбранного собеседника, получаем сообщения с любыми собеседниками
    $sql = "SELECT sender, message, timestamp FROM messages WHERE sender = ? OR recipient = ? ORDER BY timestamp";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $name);
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
$resultRequests = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: linear-gradient(to right, #f3f4f6, #e9ecef);
            display: flex;
        }

        .container {
            margin-left: 0px; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 50px;
            width: 1200px; /* Увеличиваем ширину контейнера */
        }

        h2 {
            color: #333;
        }

        .name-field {
            margin-bottom: 20px;
        }
        .box-field {
            margin-bottom: 20px;
        }
        .box-field .container_mt-4 {
            display: flex;
            justify-content: center; /* Центрирование таблицы по горизонтали */
            overflow-x: auto; /* Добавляем скролл, если таблица слишком широка */
            margin-bottom: 20px; /* Отступ снизу (при необходимости) */
        }

        .container_mt-4 {
            width: 100%; /* Заставляем таблицу занимать всю ширину контейнера */
            max-width: 100%; /* Устанавливаем максимальную ширину на 100% */
            border-collapse: collapse;
        }
        
        label {
            display: block;
            margin: 5px 0;
        }

        select, textarea {
            width: calc(100% - 20px); /* Уменьшаем ширину полей ввода на 20px (по 10px с каждой стороны для padding) */
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        input[type="text"] {
            width: calc(100% - 650px); /* Уменьшаем ширину полей ввода на 20px (по 10px с каждой стороны для padding) */
            padding: 10px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .chat-container {
            margin-left: 100px; /* Задаем отступ слева */
            width: 600px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            height: 100vh;
            overflow-y: auto;
        }

        .messages-container {
            max-height: 600px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .message {
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
        }

        .sent {
            background-color: #e9ffe9;
            text-align: right;
        }

        .received {
            background-color: #f1f1f1;
            text-align: left;
        }

        .tab {
            display: none;
        }

        .active {
            display: block;
        }
    </style>
    <script>
        function showTab(tabName) {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
        }
    </script>
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
    <div class="container">
        <div class="name-field">
            <label for="name">ФИО:</label>
            <input type="text" id="name" value="<?php echo $name; ?>" readonly><br>
            <label for="otdel">Отдел:</label>
            <input type="text" id="otdel" value="<?php echo $otdel; ?>" readonly><br>
            <label for="ip">Сетевое имя:</label>
            <input type="text" id="ip" value="<?php echo isset($ip) ? htmlspecialchars($ip) : ''; ?>" readonly><br>
            <p><a href="user_chat.php">Чат</a></p>
            <p><a href="Test2.php">тест</a></p>
        </div>

        <div class="box-field">
            <button class="tab-button" onclick="showTab('my_requests')">Мои заявки</button>
            <button class="tab-button" onclick="showTab('add_request')">Добавить новую заявку</button>
            <button class="tab-button" onclick="showTab('exit')">Выход</button>

            <div id="my_requests" class="tab active">
                <h2>Мои заявки</h2>
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
                            if ($resultRequests->num_rows > 0) {
                                while ($row = $resultRequests->fetch_assoc()) {
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

            <div id="add_request" class="tab">
                <h2>Добавить новую заявку</h2>
                <form action="save.php" method="post">
                    <label for="otdel"></label>
                    <select id="otdel1" name="otdel1" required>
                        <option value="">Выберите отдел</option>
                        <?php
                        // Подключение к базе данных
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        if ($conn->connect_error) {
                            die("Ошибка подключения: " . $conn->connect_error);
                        }

                        // Получение данных отделов
                        $sql = "SELECT DISTINCT name FROM otdel";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";
                            }
                        } else {
                            echo "<option value=''>Нет доступных отделов</option>";
                        }

                        $conn->close();
                        ?>
                    </select><br>

                    <label for="users"></label>
                    <select id="FIO" name="FIO" required>
                        <option value="">Выберите ФИО</option>
                    </select><br><br>

                    <textarea name="oshibca" id="oshibca" placeholder="Опишите проблему" required></textarea><br>

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
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";
                            }
                        } else {
                            echo "<option value=''>Нет доступных категорий</option>";
                        }

                        $conn->close();
                        ?>
                    </select><br>

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
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='" . $row["name"] . "'>" . $row["name"] . "</option>";
                            }
                        } else {
                            echo "<option value=''>Нет доступных значений</option>";
                        }

                        $conn->close();
                        ?>
                    </select><br>

                    <input type="hidden" name="done" value="отправлено"><!-- Скрытое поле для статуса -->

                    <button class="btn btn-success" type="submit">Сохранить</button>
                </form>
            </div>

            <div id="exit" class="tab">
                <h2>Выход</h2>
                <p>Вы действительно хотите выйти? <a href="exit.php">Да</a></p>
            </div>
        </div>
    </div>

    <div class="chat-container">
        <div class="messages">
            <h1>Сообщения с <?php echo htmlspecialchars(isset($recipient) ? $recipient : ''); ?></h1>
            <div class="messages-container">
                <?php foreach ($messages as $row): ?>
                    <div class="message <?php echo $row['sender'] === $name ? 'sent' : 'received'; ?>">
                        <strong><?php echo htmlspecialchars($row['sender']); ?>:</strong> <?php echo htmlspecialchars($row['message']); ?>
                        <br><small><?php echo htmlspecialchars($row['timestamp']); ?></small>
                    </div>
                <?php endforeach; ?>

                <?php if (empty($messages)): ?>
                    <div>Нет сообщений.</div>
                <?php endif; ?>
            </div>
        </div>

        <form method="post">
            <select name="recipient" required>
                <option value="">Выберите собеседника</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo htmlspecialchars($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <textarea name="message" placeholder="Введите ваше сообщение" required></textarea>
            <button type="submit">Отправить</button>
        </form>
    </div>
</body>
</html>