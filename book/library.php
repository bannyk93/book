<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Библиотека</title>
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Библиотека</h1>
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
        <h2>Все книги</h2>
        <form method="get" action="">
            <input type="text" name="author" placeholder="Автор" value="<?php echo isset($_GET['author']) ? htmlspecialchars($_GET['author']) : ''; ?>">
            <input type="text" name="category" placeholder="Категория" value="<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : ''; ?>">
            <input type="number" name="year" placeholder="Год" value="<?php echo isset($_GET['year']) ? htmlspecialchars($_GET['year']) : ''; ?>">
            <button type="submit">Фильтр</button>
        </form>
        <div class="book-list">
            <?php
            $query = "SELECT * FROM books WHERE 1";
            $params = [];

            if(!empty($_GET['author'])) {
                $query .= " AND author LIKE ?";
                $params[] = '%' . $_GET['author'] . '%';
            }

            if(!empty($_GET['category'])) {
                $query .= " AND category LIKE ?";
                $params[] = '%' . $_GET['category'] . '%';
            }

            if(!empty($_GET['year'])) {
                $query .= " AND year = ?";
                $params[] = $_GET['year'];
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);

            if($stmt->rowCount() > 0):
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
            <?php
                endwhile;
            else:
                echo "<p>Книги не найдены</p>";
            endif;
            ?>
        </div>
    </main>
</body>
</html>
