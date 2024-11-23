<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Регистрация</h1>
        <nav>
            <a href="index.php">Главная</a>
            <a href="library.php">Библиотека</a>
            <a href="login.php">Войти</a>
        </nav>
    </header>
    <main>
        <?php
        $message = '';
        if(isset($_POST['register'])) {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if($stmt->rowCount() > 0) {
                $message = "<p class='alert'>Имя пользователя или email уже заняты</p>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
                if($stmt->execute([$username, $hashed_password, $email])) {
                    $message = "<p class='success'>Регистрация прошла успешно</p>";
                } else {
                    $message = "<p class='alert'>Ошибка при регистрации</p>";
                }
            }
        }
        ?>
        <?php echo $message; ?>
        <form method="post" action="">
            <input type="text" name="username" placeholder="Логин" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="register">Зарегистрироваться</button>
        </form>
    </main>
</body>
</html>
