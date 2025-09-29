<?php
// Подключение к базе данных
$conn = new mysqli('localhost', '047582029_diplom', 'Diplom_41', 'j38202257_diplom');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");

// Получение идентификатора заявки из URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Получение данных заявки из базы данных
    $stmt = $conn->prepare("SELECT * FROM `nepoladki` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Заявка не найдена.";
        exit;
    }
} else {
    echo "Некорректный идентификатор заявки.";
    exit;
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $FIO = $_POST['FIO'] ?? '';
    $otdel = $_POST['otdel'] ?? '';
    $oshibca = $_POST['oshibca'] ?? '';
    $categoriy = $_POST['categoriy'] ?? '';
    $done = $_POST['done'] ?? '';
    $prioritet = $_POST['prioritet'] ?? '';
    $IPPC = $_POST['IPPC'] ?? '';

    // Обновление данных заявки в базе данных
    $updateStmt = $conn->prepare("UPDATE `nepoladki` SET FIO=?, otdel=?, oshibca=?, categoriy=?, done=?, prioritet=?, IPPC=? WHERE id=?");
    $updateStmt->bind_param("sssssssi", $FIO, $otdel, $oshibca, $categoriy, $done, $prioritet, $IPPC, $id);

    if ($updateStmt->execute()) {
        echo "Заявка успешно обновлена.";
    } else {
        echo "Ошибка при обновлении заявки: " . $conn->error;
    }

    $updateStmt->close();
}

// Получение списка статусов из базы данных
$statusOptions = [];
$statusResult = $conn->query("SELECT name FROM `status`");

if ($statusResult) {
    while ($rowStatus = $statusResult->fetch_assoc()) {
        $statusOptions[] = $rowStatus['name'];
    }
}

// Получение списка категорий из базы данных
$categoriyOptions = [];
$categoriyResult = $conn->query("SELECT name FROM `problems`");

if ($categoriyResult) {
    while ($rowCategoriy = $categoriyResult->fetch_assoc()) {
        $categoriyOptions[] = $rowCategoriy['name'];
    }
}

// Получение списка приоритетов из базы данных
$prioritetOptions = [];
$prioritetResult = $conn->query("SELECT name FROM `prioritet`");

if ($prioritetResult) {
    while ($rowPrioritet = $prioritetResult->fetch_assoc()) {
        $prioritetOptions[] = $rowPrioritet['name'];
    }
}

// Получение списка отделов из базы данных
$otdelOptions = [];
$otdelResult = $conn->query("SELECT name FROM `otdel`");

if ($otdelResult) {
    while ($rowOtdel = $otdelResult->fetch_assoc()) {
        $otdelOptions[] = $rowOtdel['name'];
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать заявку</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
        }

        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #4cae4c;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-align: center;
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
    
</head>
<body>
    <h1>Редактировать заявку</h1>
    <form method="POST" action="">
        <label for="FIO">ФИО:</label>
        <input type="text" id="FIO" name="FIO" value="<?php echo htmlspecialchars($row['FIO']); ?>" required><br>

        <label for="otdel">Отдел:</label>
        <select id="otdel" name="otdel" required>
            <?php foreach ($otdelOptions as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($option === $row['otdel']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="oshibca">Ошибка:</label>
        <input type="text" id="oshibca" name="oshibca" value="<?php echo htmlspecialchars($row['oshibca']); ?>" required><br>

        <label for="categoriy">Категория:</label>
        <select id="categoriy" name="categoriy" required>
            <?php foreach ($categoriyOptions as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($option === $row['categoriy']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="done">Статус:</label>
        <select id="done" name="done" required>
            <?php foreach ($statusOptions as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($option === $row['done']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="prioritet">Приоритет:</label>
        <select id="prioritet" name="prioritet" required>
            <?php foreach ($prioritetOptions as $option): ?>
                <option value="<?php echo htmlspecialchars($option); ?>" <?php echo ($option === $row['prioritet']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label for="IPPC">Сетевое имя:</label>
        <input type="text" id="IPPC" name="IPPC" value="<?php echo htmlspecialchars($row['IPPC']); ?>" required><br>

        <input type="submit" value="Сохранить изменения">
    </form>
    <a href="poisk.php">Назад к фильтру</a>
</body>
</html>