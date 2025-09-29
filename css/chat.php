<?php
// Включение отображения ошибок (для разработки)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Получаем имя пользователя из файла cookie
$name = isset($_COOKIE['coock']) ? htmlspecialchars($_COOKIE['coock']) : null;

// Проверка, установлен ли куки
if ($name === null) {
    die("Ошибка: имя пользователя не установлено в куки.");
}

// Параметры подключения к базе данных
$servername = "localhost";
$username = "047582029_diplom";
$password = "Diplom_41";
$dbname = "j38202257_diplom";

// Подключаемся к базе данных
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка подключения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получаем список пользователей
$userListSql = "SELECT name FROM users";
$userListStmt = $conn->prepare($userListSql);
$userListStmt->execute();
$userListResult = $userListStmt->get_result();
$userList = $userListResult->fetch_all(MYSQLI_ASSOC);
$userListStmt->close();

// Инициализируем переменную для выбранного пользователя
$selectedUser = null;

// Получаем выбранного пользователя и сообщения, если он был выбран
if (isset($_GET['user'])) {
    $selectedUser = htmlspecialchars($_GET['user']);
}

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['receiver'])) {
    $receiver = htmlspecialchars($_POST['receiver']);
    $message = htmlspecialchars($_POST['message']);
    
    // Подготовка и выполнение запроса
    $stmt = $conn->prepare("INSERT INTO messages (sender, recipient, message, timestamp) VALUES (?, ?, ?, NOW())");

    if ($stmt === false) {
        die("Ошибка подготовки: " . $conn->error);
    }

    $stmt->bind_param("sss", $name, $receiver, $message);
    $stmt->execute();
    
    if ($stmt->error) {
        echo "Ошибка при отправке сообщения: " . $stmt->error;
    }

    $stmt->close();
}

// Получаем сообщения из базы данных, если выбран пользователь
if ($selectedUser) {
    $sql = "SELECT * FROM messages WHERE (sender = ? AND recipient = ?) OR (sender = ? AND recipient = ?) ORDER BY timestamp DESC";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Ошибка подготовки: " . $conn->error);
    }

    $stmt->bind_param("ssss", $name, $selectedUser, $selectedUser, $name);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = null; // Если никто не выбран, сообщения нет
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Сообщения</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 20px;
            color: #333;
            display: flex;
            justify-content: center;
        }
        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
        }
        .chat-container {
            flex: 2; /* Занимает две трети ширины */
            margin-right: 20px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        .messages {
            flex: 1; /* Занимает оставшееся пространство */
            overflow: hidden;
            margin-bottom: 20px;
        }
        .users-list {
            flex: 1; /* Занимает одну треть ширины */
            padding: 10px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            color: #007bff;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        .message.sent {
            background: #d1ecf1;
            text-align: right;
        }
        .message.received {
            background: #f8d7da;
            text-align: left;
        }
        .messages-container {
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }
        .form-container {
            margin-top: 10px;
        }
        textarea {
            width: 100%;
            height: 60px;
            resize: none;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 10px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
            width: 100%;
        }
        button:hover {
            background-color: #0056b3;
        }
        .users-list ul {
            list-style-type: none;
            padding: 0;
        }
        .users-list li {
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
            background: #f7f7f7;
            transition: background 0.3s;
        }
        .users-list li:hover {
            background: #e2e6ea;
        }
        @media (max-width: 600px) {
            .container {
                flex-direction: column;
            }
            .chat-container {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="chat-container">
        <div class="messages">
            <h1>Сообщения с <?php echo htmlspecialchars($selectedUser ? $selectedUser : ''); ?></h1>
            
            <div class="messages-container">
                <?php
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $msgClass = $row['sender'] === $name ? 'sent' : 'received';
                        echo "<div class='message $msgClass'><strong>{$row['sender']}:</strong> {$row['message']} <br><small>{$row['timestamp']}</small></div>";
                    }
                } else if ($selectedUser) {
                    echo "<div class='message received'>Нет сообщений с этим пользователем.</div>";
                }
                ?>
            </div>

            <div class="form-container">
                <form method="post">
                    <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($selectedUser); ?>">
                    <textarea name="message" placeholder="Введите ваше сообщение" required></textarea>
                    <button type="submit">Отправить</button>
                </form>
            </div>
        </div>
    </div>

    <div class="users-list">
        <h2>Пользователи</h2>
        <ul>
            <?php foreach ($userList as $user): ?>
                <li><a href="?user=<?php echo urlencode($user['name']); ?>"><?php echo htmlspecialchars($user['name']); ?></a></li>
            <?php endforeach; ?>
        <li><a href="chat.php" style="color: black;">Назад</a></li>
        <li><a href="menu.php" style="color: black;">Меню</a></li>
        </ul>
        
    </div>
</div>

</body>
</html>

<?php
// Закрываем соединение
$conn->close();
?>