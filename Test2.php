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

            <p><a href="user_chat.php">Тест</a></p>

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

        <?php
$host = "localhost";
$user = "047582029_diplom";
$pass = "Diplom_41";
$db = "j38202257_diplom";

// Создаем подключение к базе данных
$conn = new mysqli($host, $user, $pass, $db);

// Проверяем соединение
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Запрашиваем имена пользователей из отдела "Техническая поддержка"
$sql = "SELECT id, name FROM users WHERE otdel = 'Техническая поддержка'";
$result = $conn->query($sql);

$users = [];

// Если есть результаты, заполняем массив пользователей
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row; // Добавляем каждого пользователя в массив
    }
}

// Инициализируем переменную для выбранного пользователя
$selectedUser = null;

// Получаем выбранного пользователя, если он был выбран
if (isset($_GET['user'])) {
    $selectedUser = htmlspecialchars($_GET['user']);
}

// Получаем имя авторизованного пользователя из куки
$name = isset($_COOKIE['coock']) ? htmlspecialchars($_COOKIE['coock']) : null;

// Проверяем, авторизован ли пользователь
if (!$name) {
    die("Вы не авторизованы. Пожалуйста, войдите в систему.");
}

// Получаем сообщения из базы данных
$messages = [];
if ($selectedUser) {
    // Если выбран конкретный пользователь, получаем сообщения только с ним
    $sql = "SELECT * FROM messages WHERE ((sender = ? AND recipient = ?) OR (sender = ? AND recipient = ?)) AND is_read = 0 ORDER BY timestamp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $selectedUser, $selectedUser, $name);
} else {
    // Если не выбран пользователь, получаем все непрочитанные сообщения
    $sql = "SELECT * FROM messages WHERE (recipient = ? OR sender = ?) AND is_read = 0 ORDER BY timestamp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $name);
}

if ($stmt === false) {
    die("Ошибка подготовки: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $messages[] = $row; // Добавляем каждое сообщение в массив
}

$stmt->close();

// Обработка отправки ответа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reply_message'])) {
        $replyMessage = htmlspecialchars($_POST['reply_message']);
        $messageId = intval($_POST['message_id']);

        // Обновляем статус сообщения на прочитанное
        $sql = "UPDATE messages SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $stmt->close();

        // Вставляем ответ в базу данных
        $sql = "INSERT INTO messages (sender, recipient, message, is_read) VALUES (?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $selectedUser, $replyMessage);
        $stmt->execute();
        $stmt->close();

        // Перенаправляем обратно, чтобы избежать повторной отправки формы
        header("Location: ?user=" . urlencode($selectedUser));
        exit();
    }

    if (isset($_POST['mark_as_read'])) {
        $messageId = intval($_POST['message_id']);

        // Обновляем статус сообщения на прочитанное
        $sql = "UPDATE messages SET is_read = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $messageId);
        $stmt->execute();
        $stmt->close();

        // Перенаправляем обратно, чтобы избежать повторной отправки формы
        header("Location: ?user=" . urlencode($selectedUser));
        exit();
    }

    if (isset($_POST['new_message'])) {
        $newMessage = htmlspecialchars($_POST['new_message']);

        // Вставляем новое сообщение в базу данных
        $sql = "INSERT INTO messages (sender, recipient, message, is_read) VALUES (?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $selectedUser, $newMessage);
        $stmt->execute();
        $stmt->close();

        // Перенаправляем обратно, чтобы избежать повторной отправки формы
        header("Location: ?user=" . urlencode($selectedUser));
        exit();
    }
}

// Закрываем соединение
$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .message { margin: 5px 0; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        .users-list { margin-bottom: 20px; }
        .reply-form { display: none; margin-top: 10px; }
        .new-message-form { display: none; margin-top: 10px; }
    </style>
    <script>
    const displayedMessageIds = new Set(); // Используем Set для хранения идентификаторов сообщений

    function toggleReplyForm(messageId) {
        const form = document.getElementById('reply-form-' + messageId);
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

    function toggleNewMessageForm() {
        const form = document.getElementById('new-message-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }

        let lastMessageId = 0; // Переменная для хранения последнего идентификатора сообщения

function fetchNewMessages() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'get_new_messages.php?last_message_id=' + lastMessageId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const newMessages = JSON.parse(xhr.responseText);
            const messagesDiv = document.getElementById('messages');

            newMessages.forEach(msg => {
                if (!displayedMessageIds.has(msg.id)) {
                    displayedMessageIds.add(msg.id);
                    lastMessageId = Math.max(lastMessageId, msg.id); // Обновляем последний идентификатор сообщения

                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message';
                    messageDiv.innerHTML = `
                        <strong>${msg.sender}:</strong>
                        <p>${msg.message}</p>
                        <small>${new Date(msg.timestamp).toLocaleString()}</small>
                        <button onclick="toggleReplyForm(${msg.id})">Ответить</button>
                        <div id="reply-form-${msg.id}" class="reply-form" style="display:none;">
                            <form method="POST">
                                <input type="hidden" name="message_id" value="${msg.id}">
                                <textarea name="reply_message" required></textarea>
                                <button type="submit">Отправить</button>
                            </form>
                        </div>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="message_id" value="${msg.id}">
                            <button type="submit" name="mark_as_read">Отметить как прочитано</button>
                        </form>
                    `;
                    messagesDiv.prepend(messageDiv);
                }
            });
        }
    };
    xhr.send();
}

    // Запускаем функцию каждые 10 секунд
    setInterval(fetchNewMessages, 10000);
</script>
</head>
<body>
    <h1>Чат</h1>

    <div class="users-list">
        <h2>Пользователи</h2>
        <ul>
            <li><a href="?">Все</a></li> <!-- Ссылка на вкладку "Все" -->
            <?php foreach ($users as $user): ?>
                <li><a href="?user=<?php echo urlencode($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <h2>Непрочитанные сообщения с <?php echo htmlspecialchars($selectedUser ? $selectedUser : ''); ?></h2>
    <div id="messages">
        <?php if ($messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message">
                    <strong><?php echo htmlspecialchars($msg['sender']); ?>:</strong>
                    <p><?php echo htmlspecialchars($msg['message']); ?></p>
                    <small><?php echo date('Y-m-d H:i:s', strtotime($msg['timestamp'])); ?></small>
                    <button onclick="toggleReplyForm(<?php echo $msg['id']; ?>)">Ответить</button>
                    <div id="reply-form-<?php echo $msg['id']; ?>" class="reply-form">
                        <form method="POST">
                            <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                            <textarea name="reply_message" required></textarea>
                            <button type="submit">Отправить</button>
                        </form>
                    </div>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                        <button type="submit" name="mark_as_read">Отметить как прочитано</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Нет непрочитанных сообщений.</p>
        <?php endif; ?>
        <button onclick="toggleNewMessageForm()">Написать сообщение</button>
        <div id="new-message-form" class="new-message-form">
            <form method="POST">
                <textarea name="new_message" required></textarea>
                <button type="submit">Отправить</button>
            </form>
        </div>
    </div>
</body>
</html>

    </div>

</body>

</html>