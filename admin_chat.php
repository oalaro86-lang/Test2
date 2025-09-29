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

// Запрашиваем имена пользователей
$sql = "SELECT id, name FROM users";
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