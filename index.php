<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма авторизации</title>
    <link rel="stylesheet" href="css/help.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa; /* Фоновый цвет для страницы */
        }
        .name-field {
            margin-top: 60px; /* Отступ сверху для формы */
            text-align: center;
            background-color: #000000; /* Черный фон */
            color: white; /* Белый текст */
            padding: 15px; /* Отступ внутри бокса */
            border-radius: 10px; /* Скругленные углы */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Тень для формы */
        }
        .login-box {
            margin-top: 20px; /* Отступ сверху */
            text-align: center; /* Центрирование текста */
            background-color: #000000; /* Фон для формы */
            padding: 20px;
            border-radius: 10px;
            box-shadow: 10 10 10px rgba(0, 0, 0, 0.1); /* Тень для формы */
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <?php if(empty($_COOKIE['coock'])): // Проверка на существование cookie ?>
        <div class="col">
            <div class="login-box">
                <h2>Форма авторизации</h2>
                <form action="auth.php" method="post">
                    <div class="user-box">
                        <input type="text" class="form-control" name="login" id="login" placeholder="Введите логин" required><br>
                    </div>
                    <div class="user-box">
                        <input type="password" class="form-control" name="pass" id="pass" placeholder="Введите пароль" required><br>
                    </div>
                    <button class="btn btn-success" type="submit">Авторизоваться</button><br>
                    <p>
                        <a href="reg.php">Зарегистрироваться</a>
                    </p>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="name-field">
            <p>С возвращением, <?= htmlspecialchars($_COOKIE['coock']) ?>. Чтобы выйти из своей учетной записи, нажмите: <a href="/exit.php" style="color: white;">ВЫЙТИ</a>.</p>
            
            <?php if ($_COOKIE['otdel'] === 'Техническая поддержка'): ?>
                <p>Вы находитесь в отделе Технической поддержки. Чтобы перейти к поиску, нажмите на: <a href="/poisk.php" style="color: white;">ПОИСК</a>.</p>
            <?php else: ?>
                <p>Чтобы перейти в меню нажмите на: <a href="/menu.php" style="color: white;">МЕНЮ</a>.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>