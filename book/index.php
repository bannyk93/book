<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Книжный магазин</title>
    <link rel="stylesheet" href="style.css">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Книжный магазин</h1>
        <?php
        if(isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $notif_stmt->execute([$user_id]);
            $unread_count = $notif_stmt->fetchColumn();
        }
        ?>
        <nav>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php">Главная</a>
                <a href="library.php">Библиотека</a>
                <a href="my_books.php">Мои книги</a>
                <a href="notifications.php">Уведомления<?php if($unread_count > 0) echo " ({$unread_count})"; ?></a>
                <a href="wallet.php">Кошелек</a>
                <?php if($_SESSION['is_admin']): ?>
                    <a href="adm.php">Админ</a>
                <?php endif; ?>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="index.php">Главная</a>
                <a href="library.php">Библиотека</a>
                <a href="register.php">Регистрация</a>
                <a href="login.php">Войти</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <h2>Новинки</h2>
        <div class="book-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM books ORDER BY date_added DESC LIMIT 6");
            while($row = $stmt->fetch()):
            ?>
                <div class="book">
                    <h3><?php echo htmlspecialchars($row['book_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['author']); ?>, <?php echo htmlspecialchars($row['year']); ?></p>
                    <p class="price">Цена покупки: <?php echo htmlspecialchars($row['price_buy']); ?> руб.</p>
                    <a href="buy.php?id=<?php echo $row['id']; ?>" class="button">Купить</a>
                    <p>Аренда:</p>
                    <a href="rent.php?id=<?php echo $row['id']; ?>&type=2w" class="button">2 недели</a>
                    <a href="rent.php?id=<?php echo $row['id']; ?>&type=1m" class="button">Месяц</a>
                    <a href="rent.php?id=<?php echo $row['id']; ?>&type=3m" class="button">3 месяца</a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
    <footer>
        &copy; <?php echo date('Y'); ?> Книжный магазин
    </footer>
</body>
</html>
