<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма для тех.потдержки</title>
    <link rel="stylesheet" href="css/help.css">
    <script>
        function checkNewRecords() {
            fetch('check_new_records.php')
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        let message = "Новые записи:\n";
                        data.forEach(record => {
                            message += `ID: ${record.id}, Ошибка: ${record.oshibca}, Дата: ${record.DateTime}\n`;
                        });
                        alert(message); // Отображаем уведомление
                    }
                })
                .catch(error => console.error('Ошибка:', error));
        }

        // Проверяем новые записи каждые 3 минуты (180000 мс)
        setInterval(checkNewRecords, 30000);
    </script>
</head>
<body>
    <div class="container mt-4">
        <div class="col">
            <div class="login-box">
                <h1>Форма для тех.потдержки</h1>
                <form action="newauth.php" method="post">
                    <div class="user-box">
                        <input type="text" class="form-control" name="login2" id="login2" placeholder="Введите логин"><br>
                    </div>
                    <div class="user-box">
                        <input type="password" class="form-control" name="pass2" id="pass2" placeholder="Введите пароль"><br>
                    </div>
                    <button class="btn btn-success" type="submit">Авторизоваться</button><br>
                </form>
            </div>
        </div>     
    </div>
</body>
</html>