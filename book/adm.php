<?php include 'db.php';
if(!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Админ панель</title>
    <link rel="stylesheet" href="style.css">
    <!-- Подключение Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500&display=swap">
</head>
<body>
    <header>
        <h1>Админ панель</h1>
        <?php
        $user_id = $_SESSION['user_id'];
        $notif_stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $notif_stmt->execute([$user_id]);
        $unread_count = $notif_stmt->fetchColumn();
        ?>
        <nav>
            <a href="index.php">Главная</a>
            <a href="library.php">Библиотека</a>
            <a href="admin.php">Админ</a>
            <a href="notifications.php">Уведомления<?php if($unread_count > 0) echo " ({$unread_count})"; ?></a>
            <a href="logout.php">Выйти</a>
        </nav>
    </header>
    <main>
        <?php
        $message = '';
        if(isset($_POST['add_book'])) {
            $book_name = $_POST['book_name'];
            $author = $_POST['author'];
            $year = $_POST['year'];
            $category = $_POST['category'];
            $price_buy = $_POST['price_buy'];
            $rent_two_w = $_POST['rent_two_w'];
            $rent_month = $_POST['rent_month'];
            $rent_three_m = $_POST['rent_three_m'];

            $stmt = $pdo->prepare("INSERT INTO books (book_name, author, year, category, price_buy, rent_two_w, rent_month, rent_three_m) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if($stmt->execute([$book_name, $author, $year, $category, $price_buy, $rent_two_w, $rent_month, $rent_three_m])) {
                $message = "<p class='success'>Книга добавлена</p>";
            } else {
                $message = "<p class='alert'>Ошибка при добавлении книги</p>";
            }
        }

        if(isset($_GET['delete'])) {
            $book_id = $_GET['delete'];
            $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
            if($stmt->execute([$book_id])) {
                $message = "<p class='success'>Книга удалена</p>";
                header('Location: admin.php');
                exit();
            } else {
                $message = "<p class='alert'>Ошибка при удалении книги</p>";
            }
        }
        ?>
        <?php echo $message; ?>
        <h2>Добавить книгу</h2>
        <form method="post" action="">
            <input type="text" name="book_name" placeholder="Название книги" required>
            <input type="text" name="author" placeholder="Автор" required>
            <input type="number" name="year" placeholder="Год" required>
            <input type="text" name="category" placeholder="Категория">
            <input type="number" step="0.01" name="price_buy" placeholder="Цена покупки" required>
            <input type="number" step="0.01" name="rent_two_w" placeholder="Аренда 2 недели" required>
            <input type="number" step="0.01" name="rent_month" placeholder="Аренда месяц" required>
            <input type="number" step="0.01" name="rent_three_m" placeholder="Аренда 3 месяца" required>
            <button type="submit" name="add_book">Добавить</button>
        </form>

        <h2>Список книг</h2>
        <div class="book-list">
            <?php
            $stmt = $pdo->query("SELECT * FROM books ORDER BY date_added DESC");
            while($row = $stmt->fetch()):
            ?>
                <div class="book">
                    <h3><?php echo htmlspecialchars($row['book_name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['author']); ?>, <?php echo htmlspecialchars($row['year']); ?></p>
                    <a href="edit_book.php?id=<?php echo $row['id']; ?>" class="button">Редактировать</a>
                    <a href="admin.php?delete=<?php echo $row['id']; ?>" class="button" onclick="return confirm('Вы уверены, что хотите удалить эту книгу?');">Удалить</a>
                </div>
            <?php endwhile; ?>
        </div>
    </main>
</body>
</html>
